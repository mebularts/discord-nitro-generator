"""
License verification and management
"""
import requests
from typing import Optional, Tuple
from PyQt5.QtWidgets import QMessageBox
from PyQt5.QtCore import pyqtSignal, QObject
from loguru import logger
from config import settings
from utils import get_mac_address, load_license, save_license

class LicenseManager(QObject):
    """Modern license verification manager"""
    
    license_verified = pyqtSignal(bool)
    verification_failed = pyqtSignal(str)
    
    def __init__(self):
        super().__init__()
        self.license_key: Optional[str] = None
        
    def verify_license(self, license_key: str) -> Tuple[bool, str]:
        """
        Verify license key with server
        
        Returns:
            Tuple[bool, str]: (is_valid, message)
        """
        try:
            mac_address = get_mac_address()
            
            payload = {
                "license_key": license_key,
                "mac_address": mac_address
            }
            
            logger.info(f"Verifying license for MAC: {mac_address}")
            
            response = requests.post(
                settings.license_verification_url,
                data=payload,
                timeout=30
            )
            
            if response.status_code == 200:
                if response.text.strip() == "valid":
                    self.license_key = license_key
                    save_license(license_key)
                    logger.info("License verification successful")
                    return True, "License verified successfully"
                else:
                    logger.warning("License verification failed: Invalid license")
                    return False, "Invalid license key"
            else:
                logger.error(f"License verification failed: HTTP {response.status_code}")
                return False, f"Server error: {response.status_code}"
                
        except requests.exceptions.Timeout:
            logger.error("License verification timeout")
            return False, "Connection timeout. Please check your internet connection."
        except requests.exceptions.ConnectionError:
            logger.error("License verification connection error")
            return False, "Connection error. Please check your internet connection."
        except Exception as e:
            logger.error(f"License verification error: {e}")
            return False, f"Verification error: {str(e)}"
    
    def check_saved_license(self) -> bool:
        """Check if there's a saved valid license"""
        try:
            saved_license = load_license()
            if saved_license:
                is_valid, _ = self.verify_license(saved_license)
                if is_valid:
                    self.license_key = saved_license
                    return True
        except Exception as e:
            logger.error(f"Error checking saved license: {e}")
        return False
    
    def is_license_valid(self) -> bool:
        """Check if current license is valid"""
        return self.license_key is not None
    
    def get_license_key(self) -> Optional[str]:
        """Get current license key"""
        return self.license_key
    
    def clear_license(self) -> bool:
        """Clear saved license"""
        try:
            self.license_key = None
            # Remove license file
            if settings.license_file.exists():
                settings.license_file.unlink()
            logger.info("License cleared")
            return True
        except Exception as e:
            logger.error(f"Error clearing license: {e}")
            return False