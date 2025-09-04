"""
Modern MackolApp - Main Application
"""
import sys
import os
from pathlib import Path
from typing import Optional, List, Tuple
from PyQt5.QtWidgets import (
    QApplication, QMainWindow, QVBoxLayout, QHBoxLayout, 
    QLabel, QPushButton, QWidget, QTabWidget, QMessageBox,
    QProgressBar, QTextEdit, QSplitter
)
from PyQt5.QtGui import QIcon, QFont
from PyQt5.QtCore import Qt, QThread, pyqtSignal, QTimer
from loguru import logger

# Import our modules
from config import settings
from utils import setup_logging, ensure_directory_exists, load_proxy, save_proxy
from license_manager import LicenseManager
from ui_components import (
    ModernButton, ModernLineEdit, ModernTextEdit, ModernGroupBox,
    FileSelectorWidget, ProxyInputWidget, StatusWidget, ModernMessageBox
)
from mackolik_bot import MackolikBot
from outlook_bot import OutlookBot
from account_checker import AccountChecker

class WorkerThread(QThread):
    """Worker thread for background operations"""
    
    progress_updated = pyqtSignal(int, int, str, bool)  # current, total, email, success
    operation_completed = pyqtSignal(int, int)  # successful, failed
    status_updated = pyqtSignal(str, bool)  # message, is_error
    
    def __init__(self, operation_type: str, **kwargs):
        super().__init__()
        self.operation_type = operation_type
        self.kwargs = kwargs
        self.is_running = True
        
    def run(self):
        """Run the operation"""
        try:
            if self.operation_type == "mackolik_create":
                self._run_mackolik_creation()
            elif self.operation_type == "outlook_create":
                self._run_outlook_creation()
            elif self.operation_type == "account_check":
                self._run_account_check()
            elif self.operation_type == "mail_check":
                self._run_mail_check()
        except Exception as e:
            logger.error(f"Worker thread error: {e}")
            self.status_updated.emit(f"Error: {str(e)}", True)
    
    def _run_mackolik_creation(self):
        """Run Mackolik account creation"""
        try:
            email_password_list = self.kwargs['email_password_list']
            output_filename = self.kwargs['output_filename']
            proxy = self.kwargs.get('proxy')
            
            bot = MackolikBot(proxy=proxy)
            successful, failed = bot.create_multiple_accounts(
                email_password_list, 
                output_filename,
                self.progress_updated.emit
            )
            
            self.operation_completed.emit(successful, failed)
            
        except Exception as e:
            logger.error(f"Mackolik creation error: {e}")
            self.status_updated.emit(f"Error: {str(e)}", True)
    
    def _run_outlook_creation(self):
        """Run Outlook account creation"""
        try:
            email_password_list = self.kwargs['email_password_list']
            output_filename = self.kwargs['output_filename']
            proxy = self.kwargs.get('proxy')
            
            bot = OutlookBot(proxy=proxy)
            successful, failed = bot.create_multiple_accounts(
                email_password_list, 
                output_filename,
                self.progress_updated.emit
            )
            
            self.operation_completed.emit(successful, failed)
            
        except Exception as e:
            logger.error(f"Outlook creation error: {e}")
            self.status_updated.emit(f"Error: {str(e)}", True)
    
    def _run_account_check(self):
        """Run account checking"""
        try:
            account_list = self.kwargs['account_list']
            proxy = self.kwargs.get('proxy')
            
            checker = AccountChecker(proxy=proxy)
            successful, failed, email_verification, error = checker.check_multiple_accounts(
                account_list,
                self.progress_updated.emit
            )
            
            self.operation_completed.emit(successful, failed + email_verification + error)
            
        except Exception as e:
            logger.error(f"Account check error: {e}")
            self.status_updated.emit(f"Error: {str(e)}", True)
    
    def _run_mail_check(self):
        """Run mail checking (placeholder)"""
        self.status_updated.emit("Mail check not implemented yet", False)
    
    def stop(self):
        """Stop the worker thread"""
        self.is_running = False

class LicenseVerificationWindow(QMainWindow):
    """Modern license verification window"""
    
    license_verified = pyqtSignal(bool)
    
    def __init__(self):
        super().__init__()
        self.license_manager = LicenseManager()
        self.setup_ui()
        
    def setup_ui(self):
        """Setup the UI"""
        self.setWindowTitle("License Verification")
        self.setFixedSize(400, 200)
        self.setWindowIcon(QIcon(str(settings.icon_file)))
        
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        layout = QVBoxLayout(central_widget)
        layout.setSpacing(20)
        layout.setContentsMargins(30, 30, 30, 30)
        
        # Title
        title_label = QLabel("License Verification")
        title_label.setFont(QFont("Segoe UI", 16, QFont.Bold))
        title_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(title_label)
        
        # License input
        self.license_edit = ModernLineEdit("Enter your license key")
        layout.addWidget(self.license_edit)
        
        # Buttons
        button_layout = QHBoxLayout()
        
        self.verify_button = ModernButton("Verify License")
        self.verify_button.clicked.connect(self.verify_license)
        button_layout.addWidget(self.verify_button)
        
        self.telegram_button = ModernButton("Contact Telegram")
        self.telegram_button.clicked.connect(self.open_telegram)
        button_layout.addWidget(self.telegram_button)
        
        layout.addLayout(button_layout)
        
        # Status
        self.status_label = QLabel("")
        self.status_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(self.status_label)
        
        # Set modal
        self.setWindowModality(Qt.ApplicationModal)
        
        # Connect license manager signals
        self.license_manager.license_verified.connect(self.on_license_verified)
        self.license_manager.verification_failed.connect(self.on_verification_failed)
    
    def verify_license(self):
        """Verify license key"""
        license_key = self.license_edit.text().strip()
        if not license_key:
            self.status_label.setText("Please enter a license key")
            self.status_label.setStyleSheet("color: #d13438;")
            return
        
        self.verify_button.setEnabled(False)
        self.status_label.setText("Verifying license...")
        self.status_label.setStyleSheet("color: #0078d4;")
        
        # Verify in a separate thread
        is_valid, message = self.license_manager.verify_license(license_key)
        
        if is_valid:
            self.license_verified.emit(True)
            self.close()
        else:
            self.status_label.setText(message)
            self.status_label.setStyleSheet("color: #d13438;")
        
        self.verify_button.setEnabled(True)
    
    def open_telegram(self):
        """Open Telegram contact"""
        import webbrowser
        webbrowser.open(settings.telegram_contact)
    
    def on_license_verified(self, is_verified: bool):
        """Handle license verification success"""
        if is_verified:
            self.license_verified.emit(True)
            self.close()
    
    def on_verification_failed(self, message: str):
        """Handle license verification failure"""
        self.status_label.setText(message)
        self.status_label.setStyleSheet("color: #d13438;")

class MackolikAppTab(QWidget):
    """Mackolik account creation tab"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.worker_thread = None
        self.setup_ui()
        
    def setup_ui(self):
        """Setup the UI"""
        layout = QVBoxLayout(self)
        layout.setSpacing(15)
        
        # Account creation group
        creation_group = ModernGroupBox("Account Creation Settings")
        creation_layout = QVBoxLayout(creation_group)
        
        # Account count
        self.account_count_edit = ModernLineEdit("Number of accounts to create")
        creation_layout.addWidget(self.account_count_edit)
        
        # Output filename
        self.output_filename_edit = ModernLineEdit("Output filename")
        creation_layout.addWidget(self.output_filename_edit)
        
        # Email/Password file
        self.email_file_selector = FileSelectorWidget(
            "Select email:password file", 
            "Text Files (*.txt)"
        )
        self.email_file_selector.file_selected.connect(self.on_file_selected)
        creation_layout.addWidget(self.email_file_selector)
        
        layout.addWidget(creation_group)
        
        # Proxy settings
        proxy_group = ModernGroupBox("Proxy Settings")
        proxy_layout = QVBoxLayout(proxy_group)
        
        self.proxy_input = ProxyInputWidget()
        self.proxy_input.proxy_changed.connect(self.on_proxy_changed)
        proxy_layout.addWidget(self.proxy_input)
        
        layout.addWidget(proxy_group)
        
        # Control buttons
        button_layout = QHBoxLayout()
        
        self.start_button = ModernButton("Start Creation")
        self.start_button.clicked.connect(self.start_creation)
        button_layout.addWidget(self.start_button)
        
        self.stop_button = ModernButton("Stop")
        self.stop_button.setEnabled(False)
        self.stop_button.clicked.connect(self.stop_creation)
        button_layout.addWidget(self.stop_button)
        
        layout.addLayout(button_layout)
        
        # Status and progress
        self.status_widget = StatusWidget()
        layout.addWidget(self.status_widget)
        
        # Load saved proxy
        self.load_proxy()
    
    def on_file_selected(self, file_path: str):
        """Handle file selection"""
        logger.info(f"Email file selected: {file_path}")
    
    def on_proxy_changed(self, proxy_text: str):
        """Handle proxy change"""
        save_proxy(proxy_text)
    
    def load_proxy(self):
        """Load saved proxy"""
        proxy = load_proxy()
        if proxy:
            self.proxy_input.set_proxy_text(proxy)
    
    def start_creation(self):
        """Start account creation"""
        try:
            # Validate inputs
            account_count = int(self.account_count_edit.text())
            output_filename = self.output_filename_edit.text().strip()
            email_file = self.email_file_selector.get_file_path()
            
            if not output_filename:
                ModernMessageBox.warning(self, "Warning", "Please enter output filename")
                return
            
            if not email_file:
                ModernMessageBox.warning(self, "Warning", "Please select email file")
                return
            
            # Read email file
            with open(email_file, 'r', encoding='utf-8') as f:
                email_lines = [line.strip() for line in f if line.strip()]
            
            if not email_lines:
                ModernMessageBox.warning(self, "Warning", "Email file is empty")
                return
            
            # Prepare email/password list
            email_password_list = []
            for i in range(account_count):
                line = email_lines[i % len(email_lines)]
                if ':' in line:
                    email, password = line.split(':', 1)
                    email_password_list.append((email.strip(), password.strip()))
            
            # Get proxy
            proxy_list = self.proxy_input.get_proxy_list()
            proxy = proxy_list[0] if proxy_list else None
            
            # Start worker thread
            self.worker_thread = WorkerThread(
                "mackolik_create",
                email_password_list=email_password_list,
                output_filename=output_filename,
                proxy=proxy
            )
            
            self.worker_thread.progress_updated.connect(self.on_progress_updated)
            self.worker_thread.operation_completed.connect(self.on_operation_completed)
            self.worker_thread.status_updated.connect(self.on_status_updated)
            
            self.worker_thread.start()
            
            # Update UI
            self.start_button.setEnabled(False)
            self.stop_button.setEnabled(True)
            self.status_widget.show_progress(account_count)
            self.status_widget.set_status("Starting account creation...")
            
        except ValueError:
            ModernMessageBox.warning(self, "Warning", "Please enter valid account count")
        except Exception as e:
            ModernMessageBox.error(self, "Error", f"Error starting creation: {str(e)}")
    
    def stop_creation(self):
        """Stop account creation"""
        if self.worker_thread and self.worker_thread.isRunning():
            self.worker_thread.stop()
            self.worker_thread.wait()
        
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.status_widget.hide_progress()
        self.status_widget.set_status("Operation stopped")
    
    def on_progress_updated(self, current: int, total: int, email: str, success: bool):
        """Handle progress update"""
        self.status_widget.update_progress(current)
        status = "✓" if success else "✗"
        self.status_widget.set_status(f"{status} {current}/{total}: {email}")
    
    def on_operation_completed(self, successful: int, failed: int):
        """Handle operation completion"""
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.status_widget.hide_progress()
        self.status_widget.set_status(f"Completed: {successful} successful, {failed} failed")
        
        ModernMessageBox.information(
            self, 
            "Operation Completed", 
            f"Account creation completed!\n\nSuccessful: {successful}\nFailed: {failed}"
        )
    
    def on_status_updated(self, message: str, is_error: bool):
        """Handle status update"""
        self.status_widget.set_status(message, is_error)

class OutlookAppTab(QWidget):
    """Outlook account creation tab"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.worker_thread = None
        self.setup_ui()
        
    def setup_ui(self):
        """Setup the UI"""
        layout = QVBoxLayout(self)
        layout.setSpacing(15)
        
        # Account creation group
        creation_group = ModernGroupBox("Outlook Account Creation Settings")
        creation_layout = QVBoxLayout(creation_group)
        
        # Account count
        self.account_count_edit = ModernLineEdit("Number of accounts to create")
        creation_layout.addWidget(self.account_count_edit)
        
        # Output filename
        self.output_filename_edit = ModernLineEdit("Output filename")
        creation_layout.addWidget(self.output_filename_edit)
        
        # Email/Password file
        self.email_file_selector = FileSelectorWidget(
            "Select email:password file", 
            "Text Files (*.txt)"
        )
        creation_layout.addWidget(self.email_file_selector)
        
        layout.addWidget(creation_group)
        
        # Proxy settings
        proxy_group = ModernGroupBox("Proxy Settings")
        proxy_layout = QVBoxLayout(proxy_group)
        
        self.proxy_input = ProxyInputWidget()
        proxy_layout.addWidget(self.proxy_input)
        
        layout.addWidget(proxy_group)
        
        # Control buttons
        button_layout = QHBoxLayout()
        
        self.start_button = ModernButton("Start Creation")
        self.start_button.clicked.connect(self.start_creation)
        button_layout.addWidget(self.start_button)
        
        self.stop_button = ModernButton("Stop")
        self.stop_button.setEnabled(False)
        self.stop_button.clicked.connect(self.stop_creation)
        button_layout.addWidget(self.stop_button)
        
        layout.addLayout(button_layout)
        
        # Status and progress
        self.status_widget = StatusWidget()
        layout.addWidget(self.status_widget)
        
        # Load saved proxy
        self.load_proxy()
    
    def load_proxy(self):
        """Load saved proxy"""
        proxy = load_proxy()
        if proxy:
            self.proxy_input.set_proxy_text(proxy)
    
    def start_creation(self):
        """Start account creation"""
        try:
            # Validate inputs
            account_count = int(self.account_count_edit.text())
            output_filename = self.output_filename_edit.text().strip()
            email_file = self.email_file_selector.get_file_path()
            
            if not output_filename:
                ModernMessageBox.warning(self, "Warning", "Please enter output filename")
                return
            
            if not email_file:
                ModernMessageBox.warning(self, "Warning", "Please select email file")
                return
            
            # Read email file
            with open(email_file, 'r', encoding='utf-8') as f:
                email_lines = [line.strip() for line in f if line.strip()]
            
            if not email_lines:
                ModernMessageBox.warning(self, "Warning", "Email file is empty")
                return
            
            # Prepare email/password list
            email_password_list = []
            for i in range(account_count):
                line = email_lines[i % len(email_lines)]
                if ':' in line:
                    email, password = line.split(':', 1)
                    email_password_list.append((email.strip(), password.strip()))
            
            # Get proxy
            proxy_list = self.proxy_input.get_proxy_list()
            proxy = proxy_list[0] if proxy_list else None
            
            # Start worker thread
            self.worker_thread = WorkerThread(
                "outlook_create",
                email_password_list=email_password_list,
                output_filename=output_filename,
                proxy=proxy
            )
            
            self.worker_thread.progress_updated.connect(self.on_progress_updated)
            self.worker_thread.operation_completed.connect(self.on_operation_completed)
            self.worker_thread.status_updated.connect(self.on_status_updated)
            
            self.worker_thread.start()
            
            # Update UI
            self.start_button.setEnabled(False)
            self.stop_button.setEnabled(True)
            self.status_widget.show_progress(account_count)
            self.status_widget.set_status("Starting Outlook account creation...")
            
        except ValueError:
            ModernMessageBox.warning(self, "Warning", "Please enter valid account count")
        except Exception as e:
            ModernMessageBox.error(self, "Error", f"Error starting creation: {str(e)}")
    
    def stop_creation(self):
        """Stop account creation"""
        if self.worker_thread and self.worker_thread.isRunning():
            self.worker_thread.stop()
            self.worker_thread.wait()
        
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.status_widget.hide_progress()
        self.status_widget.set_status("Operation stopped")
    
    def on_progress_updated(self, current: int, total: int, email: str, success: bool):
        """Handle progress update"""
        self.status_widget.update_progress(current)
        status = "✓" if success else "✗"
        self.status_widget.set_status(f"{status} {current}/{total}: {email}")
    
    def on_operation_completed(self, successful: int, failed: int):
        """Handle operation completion"""
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.status_widget.hide_progress()
        self.status_widget.set_status(f"Completed: {successful} successful, {failed} failed")
        
        ModernMessageBox.information(
            self, 
            "Operation Completed", 
            f"Outlook account creation completed!\n\nSuccessful: {successful}\nFailed: {failed}"
        )
    
    def on_status_updated(self, message: str, is_error: bool):
        """Handle status update"""
        self.status_widget.set_status(message, is_error)

class AccountCheckTab(QWidget):
    """Account checking tab"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.worker_thread = None
        self.setup_ui()
        
    def setup_ui(self):
        """Setup the UI"""
        layout = QVBoxLayout(self)
        layout.setSpacing(15)
        
        # Account checking group
        check_group = ModernGroupBox("Account Checking Settings")
        check_layout = QVBoxLayout(check_group)
        
        # Account file
        self.account_file_selector = FileSelectorWidget(
            "Select account file (email:password)", 
            "Text Files (*.txt)"
        )
        check_layout.addWidget(self.account_file_selector)
        
        # Account text area
        self.account_text = ModernTextEdit()
        self.account_text.setPlaceholderText("Or paste account list here (email:password format)")
        check_layout.addWidget(self.account_text)
        
        layout.addWidget(check_group)
        
        # Proxy settings
        proxy_group = ModernGroupBox("Proxy Settings")
        proxy_layout = QVBoxLayout(proxy_group)
        
        self.proxy_input = ProxyInputWidget()
        proxy_layout.addWidget(self.proxy_input)
        
        layout.addWidget(proxy_group)
        
        # Control buttons
        button_layout = QHBoxLayout()
        
        self.start_button = ModernButton("Start Checking")
        self.start_button.clicked.connect(self.start_checking)
        button_layout.addWidget(self.start_button)
        
        self.stop_button = ModernButton("Stop")
        self.stop_button.setEnabled(False)
        self.stop_button.clicked.connect(self.stop_checking)
        button_layout.addWidget(self.stop_button)
        
        layout.addLayout(button_layout)
        
        # Status and progress
        self.status_widget = StatusWidget()
        layout.addWidget(self.status_widget)
        
        # Load saved proxy
        self.load_proxy()
    
    def load_proxy(self):
        """Load saved proxy"""
        proxy = load_proxy()
        if proxy:
            self.proxy_input.set_proxy_text(proxy)
    
    def start_checking(self):
        """Start account checking"""
        try:
            # Get account list
            account_list = []
            
            # From file
            account_file = self.account_file_selector.get_file_path()
            if account_file:
                with open(account_file, 'r', encoding='utf-8') as f:
                    account_list.extend([line.strip() for line in f if line.strip()])
            
            # From text area
            text_content = self.account_text.toPlainText().strip()
            if text_content:
                account_list.extend([line.strip() for line in text_content.split('\n') if line.strip()])
            
            if not account_list:
                ModernMessageBox.warning(self, "Warning", "Please provide account list")
                return
            
            # Get proxy
            proxy_list = self.proxy_input.get_proxy_list()
            proxy = proxy_list[0] if proxy_list else None
            
            # Start worker thread
            self.worker_thread = WorkerThread(
                "account_check",
                account_list=account_list,
                proxy=proxy
            )
            
            self.worker_thread.progress_updated.connect(self.on_progress_updated)
            self.worker_thread.operation_completed.connect(self.on_operation_completed)
            self.worker_thread.status_updated.connect(self.on_status_updated)
            
            self.worker_thread.start()
            
            # Update UI
            self.start_button.setEnabled(False)
            self.stop_button.setEnabled(True)
            self.status_widget.show_progress(len(account_list))
            self.status_widget.set_status("Starting account checking...")
            
        except Exception as e:
            ModernMessageBox.error(self, "Error", f"Error starting checking: {str(e)}")
    
    def stop_checking(self):
        """Stop account checking"""
        if self.worker_thread and self.worker_thread.isRunning():
            self.worker_thread.stop()
            self.worker_thread.wait()
        
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.status_widget.hide_progress()
        self.status_widget.set_status("Operation stopped")
    
    def on_progress_updated(self, current: int, total: int, email: str, success: bool):
        """Handle progress update"""
        self.status_widget.update_progress(current)
        status = "✓" if success else "✗"
        self.status_widget.set_status(f"{status} {current}/{total}: {email}")
    
    def on_operation_completed(self, successful: int, failed: int):
        """Handle operation completion"""
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.status_widget.hide_progress()
        self.status_widget.set_status(f"Completed: {successful} successful, {failed} failed")
        
        ModernMessageBox.information(
            self, 
            "Operation Completed", 
            f"Account checking completed!\n\nSuccessful: {successful}\nFailed: {failed}"
        )
    
    def on_status_updated(self, message: str, is_error: bool):
        """Handle status update"""
        self.status_widget.set_status(message, is_error)

class MackolApp(QMainWindow):
    """Main application window"""
    
    def __init__(self):
        super().__init__()
        self.license_manager = LicenseManager()
        self.setup_ui()
        self.check_license()
        
    def setup_ui(self):
        """Setup the main UI"""
        self.setWindowTitle(f"{settings.app_name} v{settings.app_version} | mebularts")
        self.setWindowIcon(QIcon(str(settings.icon_file)))
        self.setGeometry(100, 100, 800, 600)
        
        # Central widget
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Main layout
        main_layout = QVBoxLayout(central_widget)
        main_layout.setContentsMargins(20, 20, 20, 20)
        
        # Title
        title_label = QLabel(f"{settings.app_name} v{settings.app_version}")
        title_label.setFont(QFont("Segoe UI", 18, QFont.Bold))
        title_label.setAlignment(Qt.AlignCenter)
        title_label.setStyleSheet("color: #0078d4; margin-bottom: 20px;")
        main_layout.addWidget(title_label)
        
        # Tab widget
        self.tab_widget = QTabWidget()
        self.tab_widget.setFont(QFont("Segoe UI", 10))
        
        # Add tabs
        self.mackolik_tab = MackolikAppTab()
        self.tab_widget.addTab(self.mackolik_tab, "Mackolik Account Creation")
        
        self.outlook_tab = OutlookAppTab()
        self.tab_widget.addTab(self.outlook_tab, "Outlook Account Creation")
        
        self.check_tab = AccountCheckTab()
        self.tab_widget.addTab(self.check_tab, "Account Checker")
        
        main_layout.addWidget(self.tab_widget)
        
        # Status bar
        self.statusBar().showMessage("Ready")
    
    def check_license(self):
        """Check license validity"""
        if not self.license_manager.check_saved_license():
            self.show_license_verification()
        else:
            self.show()
    
    def show_license_verification(self):
        """Show license verification window"""
        self.license_window = LicenseVerificationWindow()
        self.license_window.license_verified.connect(self.on_license_verified)
        self.license_window.show()
    
    def on_license_verified(self, is_verified: bool):
        """Handle license verification"""
        if is_verified:
            self.show()
        else:
            self.close()

def main():
    """Main application entry point"""
    # Setup logging
    ensure_directory_exists(Path("logs"))
    setup_logging()
    
    # Create application
    app = QApplication(sys.argv)
    app.setApplicationName(settings.app_name)
    app.setApplicationVersion(settings.app_version)
    
    # Apply modern theme
    try:
        from qt_material import apply_stylesheet
        apply_stylesheet(app, theme='dark_blue.xml')
    except ImportError:
        logger.warning("qt_material not available, using default theme")
    
    # Create and show main window
    main_window = MackolApp()
    
    # Run application
    sys.exit(app.exec_())

if __name__ == '__main__':
    main()