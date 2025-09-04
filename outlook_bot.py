"""
Modern Outlook/Hotmail account creation bot
"""
import time
import random
from typing import Optional, List, Tuple
from pathlib import Path
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import NoSuchElementException, TimeoutException
from loguru import logger
from config import settings
from utils import generate_turkish_name, generate_username, generate_birth_date, create_output_filename
from selenium_manager import SeleniumManager

class OutlookBot:
    """Modern Outlook/Hotmail account creation bot"""
    
    def __init__(self, proxy: Optional[str] = None):
        self.selenium_manager = SeleniumManager(proxy=proxy)
        
    def create_account(
        self, 
        email: str, 
        password: str, 
        output_filename: str
    ) -> Tuple[bool, str]:
        """
        Create Mackolik account using Outlook/Hotmail
        
        Args:
            email: Email address
            password: Password
            output_filename: Output filename for account data
            
        Returns:
            Tuple[bool, str]: (success, message)
        """
        try:
            with self.selenium_manager.get_driver(use_undetected=True) as driver:
                logger.info(f"Creating account for: {email}")
                
                # Step 1: Open Mackolik website
                if not self._open_mackolik_website(driver):
                    return False, "Failed to open Mackolik website"
                
                # Step 2: Handle advertisement
                self._handle_advertisement(driver)
                
                # Step 3: Open registration form
                if not self._open_registration_form(driver):
                    return False, "Failed to open registration form"
                
                # Step 4: Fill registration form
                if not self._fill_registration_form(driver, email, password):
                    return False, "Failed to fill registration form"
                
                # Step 5: Submit registration
                if not self._submit_registration(driver):
                    return False, "Failed to submit registration"
                
                # Step 6: Handle email verification
                if not self._handle_outlook_verification(driver, email, password):
                    return False, "Failed to verify email"
                
                # Step 7: Save account data
                self._save_account_data(email, password, output_filename)
                
                logger.info(f"Account created successfully: {email}")
                return True, "Account created successfully"
                
        except Exception as e:
            logger.error(f"Error creating account for {email}: {e}")
            return False, f"Error: {str(e)}"
    
    def _open_mackolik_website(self, driver) -> bool:
        """Open Mackolik website"""
        try:
            driver.get("https://www.mackolik.com/canli-sonuclar")
            self.selenium_manager.random_delay(3, 5)
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
    
    def _open_registration_form(self, driver) -> bool:
        """Open registration form"""
        try:
            # Click login widget
            login_widget = self.selenium_manager.wait_for_clickable(
                driver, By.CSS_SELECTOR, 
                'div.widget-login[data-module="login"][data-module-loaded="true"]'
            )
            self.selenium_manager.safe_click(driver, login_widget)
            self.selenium_manager.random_delay(1, 2)
            
            # Switch to registration form
            driver.execute_script('document.querySelector(".login").style.display = "none";')
            driver.execute_script('document.querySelector(".register").style.display = "block";')
            
            return True
        except Exception as e:
            logger.error(f"Failed to open registration form: {e}")
            return False
    
    def _fill_registration_form(self, driver, email: str, password: str) -> bool:
        """Fill registration form with data"""
        try:
            # Generate random data
            first_name, last_name = generate_turkish_name()
            username = generate_username()
            day, month, year = generate_birth_date()
            
            # Fill email
            email_field = driver.find_element(By.ID, 'register-email')
            self.selenium_manager.safe_send_keys(email_field, email)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Fill first name
            first_name_field = driver.find_element(By.ID, 'register-first-name')
            self.selenium_manager.safe_send_keys(first_name_field, first_name)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Fill last name
            last_name_field = driver.find_element(By.ID, 'register-last-name')
            self.selenium_manager.safe_send_keys(last_name_field, last_name)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Fill password
            password_field = driver.find_element(By.ID, 'register-password')
            self.selenium_manager.safe_send_keys(password_field, password)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Fill password confirmation
            password_confirm_field = driver.find_element(By.ID, 'register-password-2')
            self.selenium_manager.safe_send_keys(password_confirm_field, password)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Fill username
            username_field = driver.find_element(By.ID, 'register-username')
            self.selenium_manager.safe_send_keys(username_field, username)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Select gender
            gender_field = driver.find_element(By.CSS_SELECTOR, 'select#register-gender')
            gender_field.send_keys(Keys.DOWN)
            self.selenium_manager.random_delay(0.5, 1)
            
            # Select birth date
            day_field = driver.find_element(By.CSS_SELECTOR, 'select#register-dob-day')
            day_field.send_keys(str(day))
            self.selenium_manager.random_delay(0.5, 1)
            
            month_field = driver.find_element(By.CSS_SELECTOR, 'select#register-dob-month')
            month_field.send_keys(month)
            self.selenium_manager.random_delay(0.5, 1)
            
            year_field = driver.find_element(By.CSS_SELECTOR, 'select#register-dob-year')
            year_field.send_keys(str(year))
            self.selenium_manager.random_delay(0.5, 1)
            
            logger.info(f"Registration form filled for: {email}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to fill registration form: {e}")
            return False
    
    def _submit_registration(self, driver) -> bool:
        """Submit registration form"""
        try:
            # Check all checkboxes and submit
            js_code = """
            document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.checked = true;
            });
            document.getElementById('btn-register').click();
            """
            driver.execute_script(js_code)
            
            # Wait for registration to complete
            self.selenium_manager.random_delay(20, 25)
            logger.info("Registration form submitted")
            return True
            
        except Exception as e:
            logger.error(f"Failed to submit registration: {e}")
            return False
    
    def _handle_outlook_verification(self, driver, email: str, password: str) -> bool:
        """Handle Outlook email verification"""
        try:
            # Open Outlook
            driver.get('https://login.live.com/')
            self.selenium_manager.random_delay(2, 3)
            
            # Login to Outlook
            if not self._login_to_outlook(driver, email, password):
                return False
            
            # Find and click activation email
            if not self._find_activation_email_outlook(driver):
                return False
            
            # Click activation link
            if not self._click_activation_link_outlook(driver):
                return False
            
            logger.info("Outlook email verification completed")
            return True
            
        except Exception as e:
            logger.error(f"Failed to handle Outlook verification: {e}")
            return False
    
    def _login_to_outlook(self, driver, email: str, password: str) -> bool:
        """Login to Outlook"""
        try:
            # Enter email
            email_field = driver.find_element(By.ID, 'i0116')
            self.selenium_manager.safe_send_keys(email_field, email)
            self.selenium_manager.random_delay(1, 2)
            
            # Click next
            next_button = driver.find_element(By.ID, 'idSIButton9')
            self.selenium_manager.safe_click(driver, next_button)
            self.selenium_manager.random_delay(2, 3)
            
            # Enter password
            password_field = driver.find_element(By.ID, 'i0118')
            self.selenium_manager.safe_send_keys(password_field, password)
            self.selenium_manager.random_delay(1, 2)
            
            # Click next
            password_next = driver.find_element(By.ID, 'idSIButton9')
            self.selenium_manager.safe_click(driver, password_next)
            self.selenium_manager.random_delay(3, 5)
            
            # Handle additional prompts
            self._handle_outlook_prompts(driver)
            
            return True
            
        except Exception as e:
            logger.error(f"Failed to login to Outlook: {e}")
            return False
    
    def _handle_outlook_prompts(self, driver):
        """Handle Outlook additional prompts"""
        try:
            # Handle "Tamam" button
            tamam_buttons = driver.find_elements(
                By.XPATH, '//button[@class="ms-Button ms-Button--primary root-122"]'
            )
            for button in tamam_buttons:
                try:
                    self.selenium_manager.safe_click(driver, button)
                    self.selenium_manager.random_delay(1, 2)
                except:
                    pass
            
            # Handle accept button
            try:
                accept_button = driver.find_element(By.ID, 'acceptButton')
                self.selenium_manager.safe_click(driver, accept_button)
                self.selenium_manager.random_delay(2, 3)
            except:
                pass
                
        except Exception as e:
            logger.debug(f"No additional prompts found: {e}")
    
    def _find_activation_email_outlook(self, driver) -> bool:
        """Find Mackolik activation email in Outlook"""
        try:
            # Go to inbox
            driver.get('https://outlook.live.com/mail/0/inbox')
            self.selenium_manager.random_delay(5, 7)
            
            # Find email elements
            email_divs = driver.find_elements(
                By.XPATH, '//div[@id="MailList"]//div[@data-animatable="true"]'
            )
            
            for email_div in email_divs:
                try:
                    # Look for Mackolik subject
                    subject_element = email_div.find_element(
                        By.XPATH, './/div[contains(@class, "IjzWp")]/span[contains(text(), "Mackolik - Üyelik Aktivasyonu")]'
                    )
                    
                    if subject_element:
                        self.selenium_manager.safe_click(driver, email_div)
                        self.selenium_manager.random_delay(3, 5)
                        logger.info("Mackolik activation email found in Outlook")
                        return True
                        
                except NoSuchElementException:
                    continue
            
            logger.error("Mackolik activation email not found in Outlook")
            return False
            
        except Exception as e:
            logger.error(f"Failed to find activation email in Outlook: {e}")
            return False
    
    def _click_activation_link_outlook(self, driver) -> bool:
        """Click activation link in Outlook email"""
        try:
            # Find email body
            email_body = driver.find_element(By.CSS_SELECTOR, 'div[role="document"]')
            
            # Find activation links
            activation_links = email_body.find_elements(
                By.XPATH, './/a[contains(text(), "Aktivasyon linki")]'
            )
            
            for link in activation_links:
                try:
                    link_url = link.get_attribute("href")
                    if link_url:
                        driver.get(link_url)
                        self.selenium_manager.random_delay(3, 5)
                        logger.info("Activation link clicked in Outlook")
                        return True
                except:
                    continue
            
            logger.error("No valid activation link found in Outlook email")
            return False
            
        except Exception as e:
            logger.error(f"Failed to click activation link in Outlook: {e}")
            return False
    
    def _save_account_data(self, email: str, password: str, output_filename: str):
        """Save account data to file"""
        try:
            filename = create_output_filename(output_filename)
            with open(filename, "a", encoding="utf-8") as file:
                account_data = f"{email}:{password}"
                file.write(account_data + '\n')
            logger.info(f"Account data saved to: {filename}")
        except Exception as e:
            logger.error(f"Failed to save account data: {e}")
    
    def create_multiple_accounts(
        self, 
        email_password_list: List[Tuple[str, str]], 
        output_filename: str,
        progress_callback: Optional[callable] = None
    ) -> Tuple[int, int]:
        """
        Create multiple accounts using Outlook
        
        Args:
            email_password_list: List of (email, password) tuples
            output_filename: Output filename
            progress_callback: Callback function for progress updates
            
        Returns:
            Tuple[int, int]: (successful_count, failed_count)
        """
        successful = 0
        failed = 0
        
        for i, (email, password) in enumerate(email_password_list):
            try:
                logger.info(f"Creating account {i+1}/{len(email_password_list)}: {email}")
                
                success, message = self.create_account(email, password, output_filename)
                
                if success:
                    successful += 1
                    logger.info(f"✓ Account created: {email}")
                else:
                    failed += 1
                    logger.error(f"✗ Failed to create account: {email} - {message}")
                    
                    # Save failed account to error file
                    self._save_failed_account(email, password, message)
                
                # Progress callback
                if progress_callback:
                    progress_callback(i + 1, len(email_password_list), email, success)
                
                # Delay between accounts
                if i < len(email_password_list) - 1:
                    self.selenium_manager.random_delay(3, 5)
                    
            except Exception as e:
                failed += 1
                logger.error(f"Error processing account {email}: {e}")
                self._save_failed_account(email, password, str(e))
        
        logger.info(f"Account creation completed: {successful} successful, {failed} failed")
        return successful, failed
    
    def _save_failed_account(self, email: str, password: str, error_message: str):
        """Save failed account to error file"""
        try:
            with open("failed_accounts_outlook.txt", "a", encoding="utf-8") as file:
                error_data = f"{email}:{password}: - Error: {error_message}"
                file.write(error_data + '\n')
        except Exception as e:
            logger.error(f"Failed to save error data: {e}")