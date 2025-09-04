"""
Main Window for Modern Mackolik Request Bot
"""
import asyncio
import sys
from pathlib import Path
from typing import Optional, List, Tuple, Dict, Any

from PyQt6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout,
    QLabel, QPushButton, QLineEdit, QTextEdit, QProgressBar,
    QTabWidget, QGroupBox, QFormLayout, QSpinBox, QCheckBox,
    QComboBox, QFileDialog, QMessageBox, QSplitter, QFrame,
    QScrollArea, QGridLayout, QSlider, QDial, QLCDNumber,
    QTableWidget, QTableWidgetItem, QHeaderView, QAbstractItemView
)
from PyQt6.QtGui import (
    QFont, QIcon, QPalette, QColor, QPixmap, QPainter,
    QLinearGradient, QBrush, QPen, QAction
)
from PyQt6.QtCore import (
    Qt, QThread, pyqtSignal, QTimer, QPropertyAnimation,
    QEasingCurve, QRect, QSize, QPoint, QObject, QEvent
)

from modern_config import settings
from modern_utils import utils
from modern_ui import (
    ModernButton, ModernLineEdit, ModernTextEdit, ModernGroupBox,
    ModernProgressBar, FileSelectorWidget, ProxyInputWidget,
    StatusWidget, AccountTableWidget, ModernMessageBox
)
from worker_thread import AccountCreationWorker, ProxyTestWorker

class AccountCreationTab(QWidget):
    """Account creation tab"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.worker_thread = None
        self.setup_ui()
        
    def setup_ui(self):
        """Setup the UI"""
        layout = QVBoxLayout(self)
        layout.setSpacing(20)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Title
        title_label = QLabel("🚀 Advanced Account Creation")
        title_label.setFont(QFont("Segoe UI", 18, QFont.Weight.Bold))
        title_label.setStyleSheet("color: #0078d4; margin-bottom: 10px;")
        title_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(title_label)
        
        # Main content with splitter
        splitter = QSplitter(Qt.Orientation.Horizontal)
        layout.addWidget(splitter)
        
        # Left panel - Settings
        left_panel = self.create_settings_panel()
        splitter.addWidget(left_panel)
        
        # Right panel - Results
        right_panel = self.create_results_panel()
        splitter.addWidget(right_panel)
        
        # Set splitter proportions
        splitter.setSizes([400, 600])
        
        # Status bar
        self.status_widget = StatusWidget()
        layout.addWidget(self.status_widget)
    
    def create_settings_panel(self) -> QWidget:
        """Create settings panel"""
        panel = QWidget()
        layout = QVBoxLayout(panel)
        layout.setSpacing(15)
        
        # Account settings
        account_group = ModernGroupBox("Account Settings")
        account_layout = QFormLayout(account_group)
        
        # Account count
        self.account_count_spin = QSpinBox()
        self.account_count_spin.setRange(1, 1000)
        self.account_count_spin.setValue(10)
        self.account_count_spin.setFont(QFont("Segoe UI", 10))
        account_layout.addRow("Number of accounts:", self.account_count_spin)
        
        # Output filename
        self.output_filename_edit = ModernLineEdit("accounts")
        account_layout.addRow("Output filename:", self.output_filename_edit)
        
        # Email/Password file
        self.email_file_selector = FileSelectorWidget(
            "Select email:password file", 
            "Text Files (*.txt)"
        )
        account_layout.addRow("Email file:", self.email_file_selector)
        
        layout.addWidget(account_group)
        
        # Advanced settings
        advanced_group = ModernGroupBox("Advanced Settings")
        advanced_layout = QFormLayout(advanced_group)
        
        # Concurrent requests
        self.concurrent_spin = QSpinBox()
        self.concurrent_spin.setRange(1, 20)
        self.concurrent_spin.setValue(5)
        self.concurrent_spin.setFont(QFont("Segoe UI", 10))
        advanced_layout.addRow("Concurrent requests:", self.concurrent_spin)
        
        # Request timeout
        self.timeout_spin = QSpinBox()
        self.timeout_spin.setRange(5, 120)
        self.timeout_spin.setValue(30)
        self.timeout_spin.setFont(QFont("Segoe UI", 10))
        advanced_layout.addRow("Request timeout (s):", self.timeout_spin)
        
        # Retry attempts
        self.retry_spin = QSpinBox()
        self.retry_spin.setRange(1, 10)
        self.retry_spin.setValue(3)
        self.retry_spin.setFont(QFont("Segoe UI", 10))
        advanced_layout.addRow("Retry attempts:", self.retry_spin)
        
        # Use strong passwords
        self.strong_passwords_check = QCheckBox("Use strong passwords")
        self.strong_passwords_check.setChecked(True)
        self.strong_passwords_check.setFont(QFont("Segoe UI", 10))
        advanced_layout.addRow("", self.strong_passwords_check)
        
        layout.addWidget(advanced_group)
        
        # Proxy settings
        proxy_group = ModernGroupBox("Proxy Settings")
        proxy_layout = QVBoxLayout(proxy_group)
        
        self.proxy_input = ProxyInputWidget()
        proxy_layout.addWidget(self.proxy_input)
        
        # Proxy test button
        self.test_proxy_button = ModernButton("Test Proxies")
        self.test_proxy_button.clicked.connect(self.test_proxies)
        proxy_layout.addWidget(self.test_proxy_button)
        
        layout.addWidget(proxy_group)
        
        # Control buttons
        control_group = QGroupBox("Control")
        control_layout = QVBoxLayout(control_group)
        
        button_layout = QHBoxLayout()
        
        self.start_button = ModernButton("🚀 Start Creation")
        self.start_button.clicked.connect(self.start_creation)
        button_layout.addWidget(self.start_button)
        
        self.stop_button = ModernButton("⏹️ Stop")
        self.stop_button.setEnabled(False)
        self.stop_button.clicked.connect(self.stop_creation)
        button_layout.addWidget(self.stop_button)
        
        control_layout.addLayout(button_layout)
        
        # Test connection button
        self.test_connection_button = ModernButton("🔗 Test Connection")
        self.test_connection_button.clicked.connect(self.test_connection)
        control_layout.addWidget(self.test_connection_button)
        
        layout.addWidget(control_group)
        
        layout.addStretch()
        return panel
    
    def create_results_panel(self) -> QWidget:
        """Create results panel"""
        panel = QWidget()
        layout = QVBoxLayout(panel)
        layout.setSpacing(15)
        
        # Results title
        results_title = QLabel("📊 Results")
        results_title.setFont(QFont("Segoe UI", 14, QFont.Weight.Bold))
        results_title.setStyleSheet("color: #0078d4;")
        layout.addWidget(results_title)
        
        # Account table
        self.account_table = AccountTableWidget()
        layout.addWidget(self.account_table)
        
        # Export buttons
        export_layout = QHBoxLayout()
        
        self.export_success_button = ModernButton("📤 Export Successful")
        self.export_success_button.clicked.connect(self.export_successful_accounts)
        export_layout.addWidget(self.export_success_button)
        
        self.export_all_button = ModernButton("📤 Export All")
        self.export_all_button.clicked.connect(self.export_all_accounts)
        export_layout.addWidget(self.export_all_button)
        
        self.clear_table_button = ModernButton("🗑️ Clear Table")
        self.clear_table_button.clicked.connect(self.clear_table)
        export_layout.addWidget(self.clear_table_button)
        
        layout.addLayout(export_layout)
        
        return panel
    
    def start_creation(self):
        """Start account creation"""
        try:
            # Validate inputs
            account_count = self.account_count_spin.value()
            output_filename = self.output_filename_edit.text().strip()
            email_file = self.email_file_selector.get_file_path()
            
            if not output_filename:
                ModernMessageBox.warning(self, "Warning", "Please enter output filename")
                return
            
            if not email_file:
                ModernMessageBox.warning(self, "Warning", "Please select email file")
                return
            
            # Read email file
            email_password_list = utils.load_accounts_from_file(Path(email_file))
            
            if not email_password_list:
                ModernMessageBox.warning(self, "Warning", "Email file is empty or invalid")
                return
            
            # Prepare account list
            accounts_to_create = []
            for i in range(account_count):
                email, password = email_password_list[i % len(email_password_list)]
                accounts_to_create.append((email, password))
            
            # Get proxy list
            proxy_list = self.proxy_input.get_proxy_list()
            
            # Update settings
            settings.concurrent_requests = self.concurrent_spin.value()
            settings.request_timeout = self.timeout_spin.value()
            settings.max_retries = self.retry_spin.value()
            settings.use_strong_passwords = self.strong_passwords_check.isChecked()
            
            # Start worker thread
            self.worker_thread = AccountCreationWorker(
                "create_accounts",
                email_password_list=accounts_to_create,
                proxy_list=proxy_list,
                output_filename=output_filename
            )
            
            # Connect signals
            self.worker_thread.progress_updated.connect(self.on_progress_updated)
            self.worker_thread.operation_completed.connect(self.on_operation_completed)
            self.worker_thread.status_updated.connect(self.on_status_updated)
            self.worker_thread.account_created.connect(self.on_account_created)
            
            self.worker_thread.start()
            
            # Update UI
            self.start_button.setEnabled(False)
            self.stop_button.setEnabled(True)
            self.status_widget.show_progress(account_count)
            self.status_widget.set_status("Starting account creation...", False)
            
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
        self.status_widget.set_status("Operation stopped", True)
    
    def test_connection(self):
        """Test connection"""
        try:
            proxy_list = self.proxy_input.get_proxy_list()
            proxy = proxy_list[0] if proxy_list else None
            
            self.worker_thread = AccountCreationWorker(
                "test_connection",
                proxy=proxy
            )
            
            self.worker_thread.connection_test_result.connect(self.on_connection_test_result)
            self.worker_thread.status_updated.connect(self.on_status_updated)
            
            self.worker_thread.start()
            
        except Exception as e:
            ModernMessageBox.error(self, "Error", f"Error testing connection: {str(e)}")
    
    def test_proxies(self):
        """Test proxies"""
        try:
            proxy_list = self.proxy_input.get_proxy_list()
            
            if not proxy_list:
                ModernMessageBox.warning(self, "Warning", "No proxies to test")
                return
            
            self.proxy_test_worker = ProxyTestWorker(proxy_list)
            self.proxy_test_worker.proxy_test_result.connect(self.on_proxy_test_result)
            self.proxy_test_worker.test_completed.connect(self.on_proxy_test_completed)
            
            self.proxy_test_worker.start()
            
            self.status_widget.set_status(f"Testing {len(proxy_list)} proxies...", False)
            
        except Exception as e:
            ModernMessageBox.error(self, "Error", f"Error testing proxies: {str(e)}")
    
    def on_progress_updated(self, current: int, total: int, email: str, success: bool):
        """Handle progress update"""
        self.status_widget.update_progress(current)
        status = "✓" if success else "✗"
        self.status_widget.set_status(f"{status} {current}/{total}: {email}", not success, success)
    
    def on_operation_completed(self, successful: int, failed: int, accounts: List[Dict[str, Any]]):
        """Handle operation completion"""
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.status_widget.hide_progress()
        self.status_widget.set_stats(successful, failed, successful + failed)
        
        if successful > 0:
            self.status_widget.set_status(f"Completed: {successful} successful, {failed} failed", False, True)
        else:
            self.status_widget.set_status("All accounts failed", True)
        
        ModernMessageBox.information(
            self, 
            "Operation Completed", 
            f"Account creation completed!\n\nSuccessful: {successful}\nFailed: {failed}"
        )
    
    def on_status_updated(self, message: str, is_error: bool):
        """Handle status update"""
        self.status_widget.set_status(message, is_error)
    
    def on_account_created(self, account_data: Dict[str, Any]):
        """Handle account created"""
        self.account_table.add_account(account_data)
    
    def on_connection_test_result(self, success: bool, message: str):
        """Handle connection test result"""
        if success:
            ModernMessageBox.information(self, "Connection Test", f"✅ {message}")
        else:
            ModernMessageBox.error(self, "Connection Test", f"❌ {message}")
    
    def on_proxy_test_result(self, proxy: str, success: bool, message: str):
        """Handle proxy test result"""
        status = "✅" if success else "❌"
        self.status_widget.set_details(f"{status} {proxy}: {message}")
    
    def on_proxy_test_completed(self, successful: int, failed: int):
        """Handle proxy test completion"""
        self.status_widget.set_status(f"Proxy test completed: {successful} working, {failed} failed", failed > 0, successful > 0)
    
    def export_successful_accounts(self):
        """Export successful accounts"""
        # Implementation for exporting successful accounts
        pass
    
    def export_all_accounts(self):
        """Export all accounts"""
        # Implementation for exporting all accounts
        pass
    
    def clear_table(self):
        """Clear account table"""
        self.account_table.setRowCount(0)

class SettingsTab(QWidget):
    """Settings tab"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setup_ui()
    
    def setup_ui(self):
        """Setup the UI"""
        layout = QVBoxLayout(self)
        layout.setSpacing(20)
        layout.setContentsMargins(20, 20, 20, 20)
        
        # Title
        title_label = QLabel("⚙️ Settings")
        title_label.setFont(QFont("Segoe UI", 18, QFont.Weight.Bold))
        title_label.setStyleSheet("color: #0078d4; margin-bottom: 10px;")
        title_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        layout.addWidget(title_label)
        
        # Settings content
        scroll_area = QScrollArea()
        scroll_widget = QWidget()
        scroll_layout = QVBoxLayout(scroll_widget)
        
        # General settings
        general_group = ModernGroupBox("General Settings")
        general_layout = QFormLayout(general_group)
        
        # App name
        self.app_name_edit = ModernLineEdit(settings.app_name)
        general_layout.addRow("Application Name:", self.app_name_edit)
        
        # Base URL
        self.base_url_edit = ModernLineEdit(settings.mackolik_base_url)
        general_layout.addRow("Base URL:", self.base_url_edit)
        
        scroll_layout.addWidget(general_group)
        
        # Request settings
        request_group = ModernGroupBox("Request Settings")
        request_layout = QFormLayout(request_group)
        
        # Timeout
        self.timeout_spin = QSpinBox()
        self.timeout_spin.setRange(5, 300)
        self.timeout_spin.setValue(settings.request_timeout)
        request_layout.addRow("Request Timeout (s):", self.timeout_spin)
        
        # Max retries
        self.max_retries_spin = QSpinBox()
        self.max_retries_spin.setRange(1, 20)
        self.max_retries_spin.setValue(settings.max_retries)
        request_layout.addRow("Max Retries:", self.max_retries_spin)
        
        # Concurrent requests
        self.concurrent_spin = QSpinBox()
        self.concurrent_spin.setRange(1, 50)
        self.concurrent_spin.setValue(settings.concurrent_requests)
        request_layout.addRow("Concurrent Requests:", self.concurrent_spin)
        
        scroll_layout.addWidget(request_group)
        
        # Account settings
        account_group = ModernGroupBox("Account Settings")
        account_layout = QFormLayout(account_group)
        
        # Password length
        self.min_password_spin = QSpinBox()
        self.min_password_spin.setRange(6, 20)
        self.min_password_spin.setValue(settings.min_password_length)
        account_layout.addRow("Min Password Length:", self.min_password_spin)
        
        self.max_password_spin = QSpinBox()
        self.max_password_spin.setRange(6, 20)
        self.max_password_spin.setValue(settings.max_password_length)
        account_layout.addRow("Max Password Length:", self.max_password_spin)
        
        # Strong passwords
        self.strong_passwords_check = QCheckBox("Use Strong Passwords")
        self.strong_passwords_check.setChecked(settings.use_strong_passwords)
        account_layout.addRow("", self.strong_passwords_check)
        
        scroll_layout.addWidget(account_group)
        
        # Save button
        save_button = ModernButton("💾 Save Settings")
        save_button.clicked.connect(self.save_settings)
        scroll_layout.addWidget(save_button)
        
        scroll_layout.addStretch()
        
        scroll_area.setWidget(scroll_widget)
        scroll_area.setWidgetResizable(True)
        layout.addWidget(scroll_area)
    
    def save_settings(self):
        """Save settings"""
        try:
            # Update settings
            settings.app_name = self.app_name_edit.text()
            settings.mackolik_base_url = self.base_url_edit.text()
            settings.request_timeout = self.timeout_spin.value()
            settings.max_retries = self.max_retries_spin.value()
            settings.concurrent_requests = self.concurrent_spin.value()
            settings.min_password_length = self.min_password_spin.value()
            settings.max_password_length = self.max_password_spin.value()
            settings.use_strong_passwords = self.strong_passwords_check.isChecked()
            
            ModernMessageBox.information(self, "Settings", "Settings saved successfully!")
            
        except Exception as e:
            ModernMessageBox.error(self, "Error", f"Error saving settings: {str(e)}")

class ModernMainWindow(QMainWindow):
    """Modern main window"""
    
    def __init__(self):
        super().__init__()
        self.setup_ui()
        self.setup_menu()
        self.setup_status_bar()
        
    def setup_ui(self):
        """Setup the main UI"""
        self.setWindowTitle(f"{settings.app_name} v{settings.app_version} | {settings.app_author}")
        self.setGeometry(100, 100, settings.window_width, settings.window_height)
        
        # Set window icon (if available)
        icon_path = Path("icon.ico")
        if icon_path.exists():
            self.setWindowIcon(QIcon(str(icon_path)))
        
        # Central widget
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Main layout
        main_layout = QVBoxLayout(central_widget)
        main_layout.setContentsMargins(0, 0, 0, 0)
        main_layout.setSpacing(0)
        
        # Header
        header = self.create_header()
        main_layout.addWidget(header)
        
        # Tab widget
        self.tab_widget = QTabWidget()
        self.tab_widget.setFont(QFont("Segoe UI", 10))
        
        # Add tabs
        self.account_creation_tab = AccountCreationTab()
        self.tab_widget.addTab(self.account_creation_tab, "🚀 Account Creation")
        
        self.settings_tab = SettingsTab()
        self.tab_widget.addTab(self.settings_tab, "⚙️ Settings")
        
        main_layout.addWidget(self.tab_widget)
    
    def create_header(self) -> QWidget:
        """Create header widget"""
        header = QWidget()
        header.setFixedHeight(80)
        header.setStyleSheet("""
            QWidget {
                background: qlineargradient(x1:0, y1:0, x2:1, y2:0,
                    stop:0 #0078d4, stop:1 #106ebe);
                border: none;
            }
        """)
        
        layout = QHBoxLayout(header)
        layout.setContentsMargins(20, 10, 20, 10)
        
        # Title
        title_label = QLabel(f"{settings.app_name} v{settings.app_version}")
        title_label.setFont(QFont("Segoe UI", 20, QFont.Weight.Bold))
        title_label.setStyleSheet("color: white;")
        layout.addWidget(title_label)
        
        layout.addStretch()
        
        # Author
        author_label = QLabel(f"by {settings.app_author}")
        author_label.setFont(QFont("Segoe UI", 12))
        author_label.setStyleSheet("color: rgba(255, 255, 255, 0.8);")
        layout.addWidget(author_label)
        
        return header
    
    def setup_menu(self):
        """Setup menu bar"""
        menubar = self.menuBar()
        
        # File menu
        file_menu = menubar.addMenu('File')
        
        # New action
        new_action = QAction('New', self)
        new_action.setShortcut('Ctrl+N')
        file_menu.addAction(new_action)
        
        # Open action
        open_action = QAction('Open', self)
        open_action.setShortcut('Ctrl+O')
        file_menu.addAction(open_action)
        
        file_menu.addSeparator()
        
        # Exit action
        exit_action = QAction('Exit', self)
        exit_action.setShortcut('Ctrl+Q')
        exit_action.triggered.connect(self.close)
        file_menu.addAction(exit_action)
        
        # Help menu
        help_menu = menubar.addMenu('Help')
        
        # About action
        about_action = QAction('About', self)
        about_action.triggered.connect(self.show_about)
        help_menu.addAction(about_action)
    
    def setup_status_bar(self):
        """Setup status bar"""
        self.statusBar().showMessage("Ready")
        
        # Add permanent widgets
        self.statusBar().addPermanentWidget(QLabel("Status: Ready"))
    
    def show_about(self):
        """Show about dialog"""
        about_text = f"""
        <h2>{settings.app_name} v{settings.app_version}</h2>
        <p>Advanced request-based Mackolik account creation tool</p>
        <p><b>Author:</b> {settings.app_author}</p>
        <p><b>Features:</b></p>
        <ul>
            <li>Request-based account creation (no browser required)</li>
            <li>Concurrent processing for high speed</li>
            <li>Advanced proxy support</li>
            <li>Real-time progress tracking</li>
            <li>Modern PyQt6 interface</li>
        </ul>
        <p><b>Note:</b> This tool is for educational purposes only.</p>
        """
        
        QMessageBox.about(self, "About", about_text)
    
    def closeEvent(self, event):
        """Handle close event"""
        # Stop any running operations
        if hasattr(self, 'account_creation_tab') and self.account_creation_tab.worker_thread:
            if self.account_creation_tab.worker_thread.isRunning():
                self.account_creation_tab.worker_thread.stop()
                self.account_creation_tab.worker_thread.wait()
        
        event.accept()