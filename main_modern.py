"""
Main entry point for Modern Mackolik Request Bot
"""
import sys
import asyncio
from pathlib import Path

from PyQt6.QtWidgets import QApplication, QSplashScreen
from PyQt6.QtGui import QPixmap, QPainter, QFont, QColor
from PyQt6.QtCore import Qt, QTimer
import qasync

from modern_config import settings
from modern_utils import utils
from main_window import ModernMainWindow

def create_splash_screen() -> QSplashScreen:
    """Create splash screen"""
    # Create a simple splash screen
    pixmap = QPixmap(400, 300)
    pixmap.fill(QColor(0, 120, 212))  # Blue background
    
    painter = QPainter(pixmap)
    painter.setRenderHint(QPainter.RenderHint.Antialiasing)
    
    # Draw title
    painter.setPen(QColor(255, 255, 255))
    painter.setFont(QFont("Segoe UI", 24, QFont.Weight.Bold))
    painter.drawText(pixmap.rect(), Qt.AlignmentFlag.AlignCenter, 
                    f"{settings.app_name}\nv{settings.app_version}")
    
    # Draw subtitle
    painter.setFont(QFont("Segoe UI", 12))
    painter.drawText(pixmap.rect().adjusted(0, 100, 0, 0), Qt.AlignmentFlag.AlignCenter,
                    "Advanced Request-based Account Creation")
    
    # Draw author
    painter.setFont(QFont("Segoe UI", 10))
    painter.drawText(pixmap.rect().adjusted(0, 200, 0, 0), Qt.AlignmentFlag.AlignCenter,
                    f"by {settings.app_author}")
    
    painter.end()
    
    splash = QSplashScreen(pixmap)
    splash.setWindowFlags(Qt.WindowType.SplashScreen | Qt.WindowType.FramelessWindowHint)
    return splash

def setup_application() -> QApplication:
    """Setup QApplication with modern styling"""
    app = QApplication(sys.argv)
    app.setApplicationName(settings.app_name)
    app.setApplicationVersion(settings.app_version)
    app.setOrganizationName(settings.app_author)
    
    # Set application style
    app.setStyle('Fusion')
    
    # Set modern palette
    palette = app.palette()
    palette.setColor(palette.ColorRole.Window, QColor(240, 240, 240))
    palette.setColor(palette.ColorRole.WindowText, QColor(0, 0, 0))
    palette.setColor(palette.ColorRole.Base, QColor(255, 255, 255))
    palette.setColor(palette.ColorRole.AlternateBase, QColor(245, 245, 245))
    palette.setColor(palette.ColorRole.ToolTipBase, QColor(255, 255, 255))
    palette.setColor(palette.ColorRole.ToolTipText, QColor(0, 0, 0))
    palette.setColor(palette.ColorRole.Text, QColor(0, 0, 0))
    palette.setColor(palette.ColorRole.Button, QColor(240, 240, 240))
    palette.setColor(palette.ColorRole.ButtonText, QColor(0, 0, 0))
    palette.setColor(palette.ColorRole.BrightText, QColor(255, 0, 0))
    palette.setColor(palette.ColorRole.Link, QColor(0, 120, 212))
    palette.setColor(palette.ColorRole.Highlight, QColor(0, 120, 212))
    palette.setColor(palette.ColorRole.HighlightedText, QColor(255, 255, 255))
    
    app.setPalette(palette)
    
    return app

async def main():
    """Main async function"""
    # Setup logging
    utils.ensure_directory_exists(settings.logs_dir)
    utils.ensure_directory_exists(settings.output_dir)
    utils.ensure_directory_exists(settings.config_dir)
    
    # Create application
    app = setup_application()
    
    # Create splash screen
    splash = create_splash_screen()
    splash.show()
    app.processEvents()
    
    # Simulate loading time
    await asyncio.sleep(2)
    
    # Create main window
    main_window = ModernMainWindow()
    
    # Close splash screen and show main window
    splash.finish(main_window)
    main_window.show()
    
    # Setup async event loop
    await qasync.run()
    
    return app.exec()

def main_sync():
    """Synchronous main function"""
    try:
        # Run async main
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\nApplication interrupted by user")
    except Exception as e:
        print(f"Application error: {e}")
        sys.exit(1)

if __name__ == '__main__':
    main_sync()