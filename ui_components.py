"""
Modern UI components for MackolApp
"""
from typing import Optional, Callable, List
from PyQt5.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QPushButton, 
    QLineEdit, QTextEdit, QFileDialog, QMessageBox, QGroupBox,
    QProgressBar, QComboBox, QCheckBox, QSpinBox, QFormLayout,
    QTabWidget, QSplitter, QFrame
)
from PyQt5.QtGui import QFont, QIcon, QPalette, QColor
from PyQt5.QtCore import Qt, pyqtSignal, QTimer
from loguru import logger
from config import settings

class ModernButton(QPushButton):
    """Modern styled button"""
    
    def __init__(self, text: str, icon: Optional[QIcon] = None, parent=None):
        super().__init__(text, parent)
        self.setMinimumHeight(40)
        self.setFont(QFont("Segoe UI", 10))
        
        if icon:
            self.setIcon(icon)
            
        # Modern styling
        self.setStyleSheet("""
            QPushButton {
                background-color: #0078d4;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 8px 16px;
                font-weight: 500;
            }
            QPushButton:hover {
                background-color: #106ebe;
            }
            QPushButton:pressed {
                background-color: #005a9e;
            }
            QPushButton:disabled {
                background-color: #cccccc;
                color: #666666;
            }
        """)

class ModernLineEdit(QLineEdit):
    """Modern styled line edit"""
    
    def __init__(self, placeholder: str = "", parent=None):
        super().__init__(parent)
        self.setPlaceholderText(placeholder)
        self.setMinimumHeight(35)
        self.setFont(QFont("Segoe UI", 10))
        
        # Modern styling
        self.setStyleSheet("""
            QLineEdit {
                border: 2px solid #e1e1e1;
                border-radius: 6px;
                padding: 8px 12px;
                background-color: white;
                selection-background-color: #0078d4;
            }
            QLineEdit:focus {
                border-color: #0078d4;
            }
            QLineEdit:disabled {
                background-color: #f5f5f5;
                color: #666666;
            }
        """)

class ModernTextEdit(QTextEdit):
    """Modern styled text edit"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setFont(QFont("Consolas", 9))
        
        # Modern styling
        self.setStyleSheet("""
            QTextEdit {
                border: 2px solid #e1e1e1;
                border-radius: 6px;
                padding: 8px;
                background-color: white;
                selection-background-color: #0078d4;
            }
            QTextEdit:focus {
                border-color: #0078d4;
            }
        """)

class ModernGroupBox(QGroupBox):
    """Modern styled group box"""
    
    def __init__(self, title: str = "", parent=None):
        super().__init__(title, parent)
        self.setFont(QFont("Segoe UI", 10, QFont.Bold))
        
        # Modern styling
        self.setStyleSheet("""
            QGroupBox {
                font-weight: bold;
                border: 2px solid #e1e1e1;
                border-radius: 8px;
                margin-top: 10px;
                padding-top: 10px;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 10px;
                padding: 0 8px 0 8px;
                background-color: white;
            }
        """)

class ModernProgressBar(QProgressBar):
    """Modern styled progress bar"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setMinimumHeight(20)
        
        # Modern styling
        self.setStyleSheet("""
            QProgressBar {
                border: 2px solid #e1e1e1;
                border-radius: 10px;
                text-align: center;
                background-color: #f0f0f0;
            }
            QProgressBar::chunk {
                background-color: #0078d4;
                border-radius: 8px;
            }
        """)

class FileSelectorWidget(QWidget):
    """File selector widget with modern styling"""
    
    file_selected = pyqtSignal(str)
    
    def __init__(self, placeholder: str = "Select file...", file_filter: str = "All Files (*)", parent=None):
        super().__init__(parent)
        self.file_filter = file_filter
        self.setup_ui(placeholder)
    
    def setup_ui(self, placeholder: str):
        layout = QHBoxLayout(self)
        layout.setContentsMargins(0, 0, 0, 0)
        
        self.file_edit = ModernLineEdit(placeholder)
        self.file_edit.setReadOnly(True)
        layout.addWidget(self.file_edit)
        
        self.browse_button = ModernButton("Browse")
        self.browse_button.clicked.connect(self.browse_file)
        layout.addWidget(self.browse_button)
    
    def browse_file(self):
        file_path, _ = QFileDialog.getOpenFileName(
            self, "Select File", "", self.file_filter
        )
        if file_path:
            self.file_edit.setText(file_path)
            self.file_selected.emit(file_path)
    
    def get_file_path(self) -> str:
        return self.file_edit.text()
    
    def set_file_path(self, path: str):
        self.file_edit.setText(path)

class ProxyInputWidget(QWidget):
    """Proxy input widget with validation"""
    
    proxy_changed = pyqtSignal(str)
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setup_ui()
    
    def setup_ui(self):
        layout = QVBoxLayout(self)
        layout.setContentsMargins(0, 0, 0, 0)
        
        # Proxy input
        self.proxy_edit = ModernLineEdit("Proxy List (ip:port, separated by commas)")
        self.proxy_edit.textChanged.connect(self.on_proxy_changed)
        layout.addWidget(self.proxy_edit)
        
        # Validation label
        self.validation_label = QLabel("")
        self.validation_label.setStyleSheet("color: #d13438; font-size: 9px;")
        layout.addWidget(self.validation_label)
    
    def on_proxy_changed(self, text: str):
        self.validate_proxy(text)
        self.proxy_changed.emit(text)
    
    def validate_proxy(self, proxy_text: str) -> bool:
        """Validate proxy format"""
        if not proxy_text.strip():
            self.validation_label.setText("")
            return True
        
        proxies = [p.strip() for p in proxy_text.split(',')]
        valid_count = 0
        
        for proxy in proxies:
            if ':' in proxy:
                parts = proxy.split(':')
                if len(parts) == 2 and parts[1].isdigit():
                    valid_count += 1
        
        if valid_count == len(proxies):
            self.validation_label.setText("✓ Valid proxy format")
            self.validation_label.setStyleSheet("color: #107c10; font-size: 9px;")
            return True
        else:
            self.validation_label.setText("⚠ Invalid proxy format (use ip:port)")
            self.validation_label.setStyleSheet("color: #d13438; font-size: 9px;")
            return False
    
    def get_proxy_list(self) -> List[str]:
        """Get list of proxies"""
        text = self.proxy_edit.text().strip()
        if not text:
            return []
        return [p.strip() for p in text.split(',') if p.strip()]
    
    def set_proxy_text(self, text: str):
        self.proxy_edit.setText(text)

class StatusWidget(QWidget):
    """Status display widget"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setup_ui()
    
    def setup_ui(self):
        layout = QVBoxLayout(self)
        layout.setContentsMargins(0, 0, 0, 0)
        
        # Status label
        self.status_label = QLabel("Ready")
        self.status_label.setFont(QFont("Segoe UI", 9))
        self.status_label.setStyleSheet("color: #107c10;")
        layout.addWidget(self.status_label)
        
        # Progress bar
        self.progress_bar = ModernProgressBar()
        self.progress_bar.setVisible(False)
        layout.addWidget(self.progress_bar)
    
    def set_status(self, message: str, is_error: bool = False):
        """Set status message"""
        self.status_label.setText(message)
        if is_error:
            self.status_label.setStyleSheet("color: #d13438;")
        else:
            self.status_label.setStyleSheet("color: #107c10;")
    
    def show_progress(self, maximum: int = 100):
        """Show progress bar"""
        self.progress_bar.setMaximum(maximum)
        self.progress_bar.setValue(0)
        self.progress_bar.setVisible(True)
    
    def update_progress(self, value: int):
        """Update progress bar"""
        self.progress_bar.setValue(value)
    
    def hide_progress(self):
        """Hide progress bar"""
        self.progress_bar.setVisible(False)

class ModernMessageBox:
    """Modern message box wrapper"""
    
    @staticmethod
    def information(parent, title: str, message: str):
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Information)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.Ok)
        msg.exec_()
    
    @staticmethod
    def warning(parent, title: str, message: str):
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Warning)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.Ok)
        msg.exec_()
    
    @staticmethod
    def error(parent, title: str, message: str):
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Critical)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.Ok)
        msg.exec_()
    
    @staticmethod
    def question(parent, title: str, message: str) -> bool:
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Question)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.Yes | QMessageBox.No)
        msg.setDefaultButton(QMessageBox.Yes)
        return msg.exec_() == QMessageBox.Yes