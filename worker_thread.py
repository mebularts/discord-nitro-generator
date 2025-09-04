"""
Advanced Worker Thread for Async Operations
"""
import asyncio
import random
from typing import Optional, List, Tuple, Dict, Any
from PyQt6.QtCore import QThread, pyqtSignal
from loguru import logger

from modern_config import settings
from modern_utils import utils
from request_bot import MackolikRequestBot

class AccountCreationWorker(QThread):
    """Advanced worker thread for account creation"""
    
    # Signals
    progress_updated = pyqtSignal(int, int, str, bool)  # current, total, email, success
    operation_completed = pyqtSignal(int, int, list)  # successful, failed, accounts
    status_updated = pyqtSignal(str, bool)  # message, is_error
    account_created = pyqtSignal(dict)  # account_data
    connection_test_result = pyqtSignal(bool, str)  # success, message
    
    def __init__(self, operation_type: str, **kwargs):
        super().__init__()
        self.operation_type = operation_type
        self.kwargs = kwargs
        self.is_running = True
        self.bot: Optional[MackolikRequestBot] = None
        
    def run(self):
        """Run the operation"""
        try:
            if self.operation_type == "create_accounts":
                asyncio.run(self._run_account_creation())
            elif self.operation_type == "test_connection":
                asyncio.run(self._run_connection_test())
            elif self.operation_type == "verify_accounts":
                asyncio.run(self._run_account_verification())
        except Exception as e:
            logger.error(f"Worker thread error: {e}")
            self.status_updated.emit(f"Error: {str(e)}", True)
    
    async def _run_account_creation(self):
        """Run account creation process"""
        try:
            email_password_list = self.kwargs['email_password_list']
            proxy_list = self.kwargs.get('proxy_list', [])
            output_filename = self.kwargs.get('output_filename', 'accounts')
            
            self.status_updated.emit("Starting account creation...", False)
            
            # Create bot with proxy
            proxy = random.choice(proxy_list) if proxy_list else None
            self.bot = MackolikRequestBot(proxy=proxy)
            
            # Test connection first
            connection_ok = await self.bot.test_connection()
            if not connection_ok:
                self.status_updated.emit("Connection test failed", True)
                return
            
            self.status_updated.emit("Connection established, creating accounts...", False)
            
            # Create accounts
            successful, failed, created_accounts = await self.bot.create_multiple_accounts(
                email_password_list,
                self._progress_callback
            )
            
            # Save accounts to file
            if created_accounts:
                filename = utils.create_output_filename(output_filename)
                utils.save_accounts(created_accounts, filename)
            
            # Emit completion signal
            self.operation_completed.emit(successful, failed, created_accounts)
            
            # Final status
            if successful > 0:
                self.status_updated.emit(f"Account creation completed: {successful} successful, {failed} failed", False)
            else:
                self.status_updated.emit("Account creation failed", True)
                
        except Exception as e:
            logger.error(f"Account creation error: {e}")
            self.status_updated.emit(f"Error: {str(e)}", True)
        finally:
            if self.bot:
                await self.bot.close_session()
    
    async def _run_connection_test(self):
        """Run connection test"""
        try:
            proxy = self.kwargs.get('proxy')
            self.bot = MackolikRequestBot(proxy=proxy)
            
            self.status_updated.emit("Testing connection...", False)
            
            success = await self.bot.test_connection()
            
            if success:
                self.connection_test_result.emit(True, "Connection successful")
                self.status_updated.emit("Connection test successful", False)
            else:
                self.connection_test_result.emit(False, "Connection failed")
                self.status_updated.emit("Connection test failed", True)
                
        except Exception as e:
            logger.error(f"Connection test error: {e}")
            self.connection_test_result.emit(False, f"Error: {str(e)}")
            self.status_updated.emit(f"Connection test error: {str(e)}", True)
        finally:
            if self.bot:
                await self.bot.close_session()
    
    async def _run_account_verification(self):
        """Run account verification"""
        try:
            account_list = self.kwargs['account_list']
            proxy_list = self.kwargs.get('proxy_list', [])
            
            self.status_updated.emit("Starting account verification...", False)
            
            verified_count = 0
            failed_count = 0
            
            for i, (email, password) in enumerate(account_list):
                if not self.is_running:
                    break
                
                try:
                    # Use random proxy
                    proxy = random.choice(proxy_list) if proxy_list else None
                    bot = MackolikRequestBot(proxy=proxy)
                    
                    async with bot:
                        success, message = await bot.verify_account(email, password)
                        
                        if success:
                            verified_count += 1
                            logger.info(f"✓ Account verified: {email}")
                        else:
                            failed_count += 1
                            logger.error(f"✗ Account verification failed: {email} - {message}")
                        
                        # Progress callback
                        self.progress_updated.emit(i + 1, len(account_list), email, success)
                        
                        # Random delay
                        await asyncio.sleep(random.uniform(1.0, 3.0))
                        
                except Exception as e:
                    failed_count += 1
                    logger.error(f"Error verifying account {email}: {e}")
                    self.progress_updated.emit(i + 1, len(account_list), email, False)
            
            self.operation_completed.emit(verified_count, failed_count, [])
            self.status_updated.emit(f"Verification completed: {verified_count} verified, {failed_count} failed", False)
            
        except Exception as e:
            logger.error(f"Account verification error: {e}")
            self.status_updated.emit(f"Error: {str(e)}", True)
    
    def _progress_callback(self, current: int, total: int, email: str, success: bool):
        """Progress callback for account creation"""
        self.progress_updated.emit(current, total, email, success)
        
        if success:
            self.status_updated.emit(f"✓ Account created: {email}", False)
        else:
            self.status_updated.emit(f"✗ Account creation failed: {email}", True)
    
    def stop(self):
        """Stop the worker thread"""
        self.is_running = False
        logger.info("Worker thread stop requested")

class ProxyTestWorker(QThread):
    """Worker thread for proxy testing"""
    
    proxy_test_result = pyqtSignal(str, bool, str)  # proxy, success, message
    test_completed = pyqtSignal(int, int)  # successful, failed
    
    def __init__(self, proxy_list: List[str]):
        super().__init__()
        self.proxy_list = proxy_list
        self.is_running = True
    
    def run(self):
        """Run proxy testing"""
        asyncio.run(self._run_proxy_test())
    
    async def _run_proxy_test(self):
        """Test all proxies"""
        successful = 0
        failed = 0
        
        for proxy in self.proxy_list:
            if not self.is_running:
                break
            
            try:
                bot = MackolikRequestBot(proxy=proxy)
                async with bot:
                    success = await bot.test_connection()
                    
                    if success:
                        successful += 1
                        self.proxy_test_result.emit(proxy, True, "Connection successful")
                    else:
                        failed += 1
                        self.proxy_test_result.emit(proxy, False, "Connection failed")
                        
            except Exception as e:
                failed += 1
                self.proxy_test_result.emit(proxy, False, f"Error: {str(e)}")
            
            # Small delay between tests
            await asyncio.sleep(0.5)
        
        self.test_completed.emit(successful, failed)
    
    def stop(self):
        """Stop proxy testing"""
        self.is_running = False