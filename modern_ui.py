"""
Modern PyQt6 UI for Mackolik Request Bot
"""
import asyncio
import sys
from pathlib import Path
from typing import Optional, List, Tuple, Dict, Any
from datetime import datetime

from PyQt6.QtWidgets import (
    QApplication, QMainWindow, QWidget, QVBoxLayout, QHBoxLayout,
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
from PyQt6.QtSvg import QSvgWidget

import qasync
from loguru import logger

from modern_config import settings
from modern_utils import utils
from request_bot import MackolikRequestBot

class ModernButton(QPushButton):
    """Modern styled button with animations"""
    
    def __init__(self, text: str = "", parent=None):
        super().__init__(text, parent)
        self.setMinimumHeight(45)
        self.setFont(QFont("Segoe UI", 11, QFont.Weight.Medium))
        self.setCursor(Qt.CursorShape.PointingHandCursor)
        
        # Modern styling
        self.setStyleSheet("""
            QPushButton {
                background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                    stop:0 #0078d4, stop:1 #106ebe);
                color: white;
                border: none;
                border-radius: 8px;
                padding: 12px 24px;
                font-weight: 500;
                text-align: center;
            }
            QPushButton:hover {
                background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                    stop:0 #106ebe, stop:1 #005a9e);
                transform: translateY(-1px);
            }
            QPushButton:pressed {
                background: qlineargradient(x1:0, y1:0, x2:0, y2:1,
                    stop:0 #005a9e, stop:1 #004578);
                transform: translateY(1px);
            }
            QPushButton:disabled {
                background: #cccccc;
                color: #666666;
            }
        """)
        
        # Add hover effect
        self.enterEvent = self.on_enter
        self.leaveEvent = self.on_leave
    
    def on_enter(self, event):
        """Handle mouse enter"""
        self.setStyleSheet(self.styleSheet().replace("translateY(-1px)", "translateY(-2px)"))
    
    def on_leave(self, event):
        """Handle mouse leave"""
        self.setStyleSheet(self.styleSheet().replace("translateY(-2px)", "translateY(-1px)"))

class ModernLineEdit(QLineEdit):
    """Modern styled line edit"""
    
    def __init__(self, placeholder: str = "", parent=None):
        super().__init__(parent)
        self.setPlaceholderText(placeholder)
        self.setMinimumHeight(45)
        self.setFont(QFont("Segoe UI", 10))
        
        # Modern styling
        self.setStyleSheet("""
            QLineEdit {
                border: 2px solid #e1e1e1;
                border-radius: 8px;
                padding: 12px 16px;
                background-color: white;
                selection-background-color: #0078d4;
                font-size: 14px;
            }
            QLineEdit:focus {
                border-color: #0078d4;
                box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.1);
            }
            QLineEdit:disabled {
                background-color: #f5f5f5;
                color: #666666;
                border-color: #cccccc;
            }
        """)

class ModernTextEdit(QTextEdit):
    """Modern styled text edit"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setFont(QFont("Consolas", 9))
        self.setMinimumHeight(120)
        
        # Modern styling
        self.setStyleSheet("""
            QTextEdit {
                border: 2px solid #e1e1e1;
                border-radius: 8px;
                padding: 12px;
                background-color: white;
                selection-background-color: #0078d4;
                font-family: 'Consolas', monospace;
            }
            QTextEdit:focus {
                border-color: #0078d4;
                box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.1);
            }
        """)

class ModernGroupBox(QGroupBox):
    """Modern styled group box"""
    
    def __init__(self, title: str = "", parent=None):
        super().__init__(title, parent)
        self.setFont(QFont("Segoe UI", 12, QFont.Weight.Bold))
        
        # Modern styling
        self.setStyleSheet("""
            QGroupBox {
                font-weight: bold;
                border: 2px solid #e1e1e1;
                border-radius: 12px;
                margin-top: 15px;
                padding-top: 15px;
                background-color: #fafafa;
            }
            QGroupBox::title {
                subcontrol-origin: margin;
                left: 15px;
                padding: 0 12px 0 12px;
                background-color: white;
                color: #0078d4;
            }
        """)

class ModernProgressBar(QProgressBar):
    """Modern styled progress bar"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setMinimumHeight(25)
        self.setTextVisible(True)
        
        # Modern styling
        self.setStyleSheet("""
            QProgressBar {
                border: 2px solid #e1e1e1;
                border-radius: 12px;
                text-align: center;
                background-color: #f0f0f0;
                font-weight: 500;
            }
            QProgressBar::chunk {
                background: qlineargradient(x1:0, y1:0, x2:1, y2:0,
                    stop:0 #0078d4, stop:1 #106ebe);
                border-radius: 10px;
            }
        """)

class FileSelectorWidget(QWidget):
    """Modern file selector widget"""
    
    file_selected = pyqtSignal(str)
    
    def __init__(self, placeholder: str = "Select file...", file_filter: str = "All Files (*)", parent=None):
        super().__init__(parent)
        self.file_filter = file_filter
        self.setup_ui(placeholder)
    
    def setup_ui(self, placeholder: str):
        layout = QHBoxLayout(self)
        layout.setContentsMargins(0, 0, 0, 0)
        layout.setSpacing(10)
        
        self.file_edit = ModernLineEdit(placeholder)
        self.file_edit.setReadOnly(True)
        layout.addWidget(self.file_edit, 1)
        
        self.browse_button = ModernButton("Browse")
        self.browse_button.setMaximumWidth(100)
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
    """Advanced proxy input widget"""
    
    proxy_changed = pyqtSignal(str)
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setup_ui()
    
    def setup_ui(self):
        layout = QVBoxLayout(self)
        layout.setContentsMargins(0, 0, 0, 0)
        layout.setSpacing(8)
        
        # Proxy input
        self.proxy_edit = ModernTextEdit()
        self.proxy_edit.setPlaceholderText("Enter proxy list (one per line):\nip:port\nip:port:username:password")
        self.proxy_edit.textChanged.connect(self.on_proxy_changed)
        layout.addWidget(self.proxy_edit)
        
        # Validation and info
        info_layout = QHBoxLayout()
        
        self.validation_label = QLabel("")
        self.validation_label.setFont(QFont("Segoe UI", 9))
        info_layout.addWidget(self.validation_label)
        
        self.proxy_count_label = QLabel("0 proxies")
        self.proxy_count_label.setFont(QFont("Segoe UI", 9))
        self.proxy_count_label.setStyleSheet("color: #666666;")
        info_layout.addWidget(self.proxy_count_label)
        
        info_layout.addStretch()
        layout.addLayout(info_layout)
    
    def on_proxy_changed(self):
        text = self.proxy_edit.toPlainText()
        self.validate_proxies(text)
        self.proxy_changed.emit(text)
    
    def validate_proxies(self, proxy_text: str) -> bool:
        """Validate proxy list"""
        if not proxy_text.strip():
            self.validation_label.setText("")
            self.proxy_count_label.setText("0 proxies")
            return True
        
        proxies = [line.strip() for line in proxy_text.split('\n') if line.strip()]
        valid_count = 0
        
        for proxy in proxies:
            if utils.validate_proxy(proxy):
                valid_count += 1
        
        self.proxy_count_label.setText(f"{valid_count} proxies")
        
        if valid_count == len(proxies) and valid_count > 0:
            self.validation_label.setText("✓ Valid proxy format")
            self.validation_label.setStyleSheet("color: #107c10;")
            return True
        elif valid_count > 0:
            self.validation_label.setText(f"⚠ {len(proxies) - valid_count} invalid proxies")
            self.validation_label.setStyleSheet("color: #d13438;")
            return False
        else:
            self.validation_label.setText("⚠ Invalid proxy format")
            self.validation_label.setStyleSheet("color: #d13438;")
            return False
    
    def get_proxy_list(self) -> List[str]:
        """Get list of valid proxies"""
        text = self.proxy_edit.toPlainText().strip()
        if not text:
            return []
        return [line.strip() for line in text.split('\n') if line.strip() and utils.validate_proxy(line.strip())]
    
    def set_proxy_text(self, text: str):
        self.proxy_edit.setPlainText(text)

class StatusWidget(QWidget):
    """Advanced status display widget"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setup_ui()
    
    def setup_ui(self):
        layout = QVBoxLayout(self)
        layout.setContentsMargins(0, 0, 0, 0)
        layout.setSpacing(10)
        
        # Status info
        status_layout = QHBoxLayout()
        
        self.status_icon = QLabel("🟢")
        self.status_icon.setFont(QFont("Segoe UI", 16))
        status_layout.addWidget(self.status_icon)
        
        self.status_label = QLabel("Ready")
        self.status_label.setFont(QFont("Segoe UI", 11, QFont.Weight.Medium))
        status_layout.addWidget(self.status_label)
        
        status_layout.addStretch()
        
        # Stats
        self.stats_label = QLabel("")
        self.stats_label.setFont(QFont("Segoe UI", 9))
        self.stats_label.setStyleSheet("color: #666666;")
        status_layout.addWidget(self.stats_label)
        
        layout.addLayout(status_layout)
        
        # Progress bar
        self.progress_bar = ModernProgressBar()
        self.progress_bar.setVisible(False)
        layout.addWidget(self.progress_bar)
        
        # Details
        self.details_label = QLabel("")
        self.details_label.setFont(QFont("Segoe UI", 9))
        self.details_label.setStyleSheet("color: #666666;")
        self.details_label.setWordWrap(True)
        layout.addWidget(self.details_label)
    
    def set_status(self, message: str, is_error: bool = False, is_success: bool = False):
        """Set status message"""
        self.status_label.setText(message)
        
        if is_error:
            self.status_icon.setText("🔴")
            self.status_label.setStyleSheet("color: #d13438;")
        elif is_success:
            self.status_icon.setText("🟢")
            self.status_label.setStyleSheet("color: #107c10;")
        else:
            self.status_icon.setText("🟡")
            self.status_label.setStyleSheet("color: #0078d4;")
    
    def set_stats(self, successful: int = 0, failed: int = 0, total: int = 0):
        """Set statistics"""
        if total > 0:
            self.stats_label.setText(f"✓ {successful} | ✗ {failed} | Total: {total}")
        else:
            self.stats_label.setText("")
    
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
    
    def set_details(self, details: str):
        """Set details text"""
        self.details_label.setText(details)

class AccountTableWidget(QTableWidget):
    """Modern account table widget"""
    
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setup_ui()
    
    def setup_ui(self):
        # Set columns
        self.setColumnCount(5)
        self.setHorizontalHeaderLabels([
            "Email", "Username", "Status", "Created", "Actions"
        ])
        
        # Styling
        self.setAlternatingRowColors(True)
        self.setSelectionBehavior(QAbstractItemView.SelectionBehavior.SelectRows)
        self.setSelectionMode(QAbstractItemView.SelectionMode.SingleSelection)
        
        # Header styling
        header = self.horizontalHeader()
        header.setStretchLastSection(True)
        header.setSectionResizeMode(0, QHeaderView.ResizeMode.Stretch)
        header.setSectionResizeMode(1, QHeaderView.ResizeMode.ResizeToContents)
        header.setSectionResizeMode(2, QHeaderView.ResizeMode.ResizeToContents)
        header.setSectionResizeMode(3, QHeaderView.ResizeMode.ResizeToContents)
        
        # Table styling
        self.setStyleSheet("""
            QTableWidget {
                border: 2px solid #e1e1e1;
                border-radius: 8px;
                background-color: white;
                gridline-color: #f0f0f0;
                selection-background-color: #0078d4;
            }
            QTableWidget::item {
                padding: 8px;
                border-bottom: 1px solid #f0f0f0;
            }
            QTableWidget::item:selected {
                background-color: #0078d4;
                color: white;
            }
            QHeaderView::section {
                background-color: #f8f9fa;
                padding: 12px;
                border: none;
                border-bottom: 2px solid #e1e1e1;
                font-weight: 600;
            }
        """)
    
    def add_account(self, account_data: Dict[str, Any]):
        """Add account to table"""
        row = self.rowCount()
        self.insertRow(row)
        
        # Email
        self.setItem(row, 0, QTableWidgetItem(account_data.get('email', '')))
        
        # Username
        self.setItem(row, 1, QTableWidgetItem(account_data.get('username', '')))
        
        # Status
        status_item = QTableWidgetItem(account_data.get('status', 'Unknown'))
        if account_data.get('status') == 'created':
            status_item.setBackground(QColor(200, 255, 200))
        elif account_data.get('status') == 'failed':
            status_item.setBackground(QColor(255, 200, 200))
        self.setItem(row, 2, status_item)
        
        # Created
        created = account_data.get('created_at', '')
        if created:
            try:
                dt = datetime.fromisoformat(created.replace('Z', '+00:00'))
                created = dt.strftime('%H:%M:%S')
            except:
                pass
        self.setItem(row, 3, QTableWidgetItem(created))
        
        # Actions
        actions_widget = QWidget()
        actions_layout = QHBoxLayout(actions_widget)
        actions_layout.setContentsMargins(5, 5, 5, 5)
        
        copy_btn = QPushButton("📋")
        copy_btn.setToolTip("Copy account info")
        copy_btn.setMaximumSize(30, 30)
        copy_btn.clicked.connect(lambda: self.copy_account_info(row))
        actions_layout.addWidget(copy_btn)
        
        self.setCellWidget(row, 4, actions_widget)
    
    def copy_account_info(self, row: int):
        """Copy account info to clipboard"""
        email = self.item(row, 0).text()
        username = self.item(row, 1).text()
        account_info = f"{email}:{username}"
        
        clipboard = QApplication.clipboard()
        clipboard.setText(account_info)
        
        # Show brief feedback
        self.item(row, 0).setBackground(QColor(200, 255, 200))
        QTimer.singleShot(1000, lambda: self.item(row, 0).setBackground(QColor(255, 255, 255)))

class ModernMessageBox:
    """Modern message box wrapper"""
    
    @staticmethod
    def information(parent, title: str, message: str):
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Icon.Information)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.StandardButton.Ok)
        msg.exec()
    
    @staticmethod
    def warning(parent, title: str, message: str):
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Icon.Warning)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.StandardButton.Ok)
        msg.exec()
    
    @staticmethod
    def error(parent, title: str, message: str):
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Icon.Critical)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.StandardButton.Ok)
        msg.exec()
    
    @staticmethod
    def question(parent, title: str, message: str) -> bool:
        msg = QMessageBox(parent)
        msg.setIcon(QMessageBox.Icon.Question)
        msg.setWindowTitle(title)
        msg.setText(message)
        msg.setStandardButtons(QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No)
        msg.setDefaultButton(QMessageBox.StandardButton.Yes)
        return msg.exec() == QMessageBox.StandardButton.Yes