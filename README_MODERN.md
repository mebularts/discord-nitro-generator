# 🚀 Modern Mackolik Request Bot v3.0.0

**Ultra-fast, request-based Mackolik account creation tool with modern PyQt6 interface**

## ✨ Features

### 🎯 **Core Features**
- **Request-based**: No browser required - ultra-fast account creation
- **Concurrent Processing**: Create multiple accounts simultaneously
- **Modern PyQt6 UI**: Beautiful, responsive interface with dark theme
- **Real-time Progress**: Live progress tracking and statistics
- **Advanced Proxy Support**: Rotate proxies automatically
- **Smart Error Handling**: Automatic retry with exponential backoff

### 🔧 **Advanced Features**
- **Async/Await**: Full async support for maximum performance
- **Type Hints**: Complete type annotation for better code quality
- **Configuration Management**: Centralized settings with Pydantic validation
- **Logging**: Advanced logging with Loguru
- **Account Validation**: Built-in account verification
- **Export Functions**: Export successful accounts to various formats

### 🎨 **UI Features**
- **Material Design**: Modern, intuitive interface
- **Tabbed Interface**: Organized workflow
- **Progress Bars**: Real-time progress visualization
- **Account Table**: Live account creation results
- **Settings Panel**: Easy configuration management
- **Splash Screen**: Professional startup experience

## 🚀 Installation

### Prerequisites
- Python 3.8 or higher
- Internet connection
- Valid email accounts

### Quick Setup

1. **Install dependencies**
```bash
pip install -r requirements_modern.txt
```

2. **Run the application**
```bash
python main_modern.py
```

## 📖 Usage

### 1. **Account Creation**
1. Go to "🚀 Account Creation" tab
2. Set number of accounts to create
3. Select email:password file
4. Configure proxy settings (optional)
5. Click "🚀 Start Creation"

### 2. **Settings Configuration**
1. Go to "⚙️ Settings" tab
2. Adjust request timeout, retry attempts, etc.
3. Configure password generation settings
4. Save settings

### 3. **Proxy Management**
- Add proxies in format: `ip:port` or `ip:port:username:password`
- Test proxies before use
- Automatic proxy rotation

## 📁 File Formats

### Email:Password File
```
email1@domain.com:password1
email2@domain.com:password2
email3@domain.com:password3
```

### Proxy Format
```
ip1:port1
ip2:port2:username:password
ip3:port3
```

## ⚙️ Configuration

### Environment Variables
Create `.env` file:
```env
# Application
APP_NAME=Mackolik Request Bot
APP_VERSION=3.0.0
APP_AUTHOR=mebularts

# API Settings
MACKOLIK_BASE_URL=https://www.mackolik.com
REQUEST_TIMEOUT=30
MAX_RETRIES=3
CONCURRENT_REQUESTS=5

# Account Settings
MIN_PASSWORD_LENGTH=8
MAX_PASSWORD_LENGTH=16
USE_STRONG_PASSWORDS=true

# Proxy Settings
USE_PROXY=false
PROXY_ROTATION=true
PROXY_TIMEOUT=10
```

## 🏗️ Architecture

### **Request-based Approach**
- Uses `httpx` for async HTTP requests
- No browser automation (Selenium)
- 10x faster than browser-based bots
- Lower resource usage

### **Async Processing**
- Full async/await support
- Concurrent account creation
- Non-blocking UI operations
- Efficient resource management

### **Modern UI**
- PyQt6 with Material Design
- Responsive layout
- Real-time updates
- Professional appearance

## 📊 Performance

### **Speed Comparison**
- **Browser-based**: ~30-60 seconds per account
- **Request-based**: ~3-5 seconds per account
- **Concurrent**: 5-20 accounts simultaneously

### **Resource Usage**
- **Memory**: ~50MB (vs 200MB+ for browser)
- **CPU**: Minimal usage
- **Network**: Optimized requests

## 🔧 Advanced Usage

### **Custom Headers**
Modify `request_bot.py` to add custom headers:
```python
headers = {
    'User-Agent': 'Custom User Agent',
    'X-Custom-Header': 'value'
}
```

### **Custom Retry Logic**
Configure retry behavior in `modern_config.py`:
```python
max_retries: int = 5
retry_delay: float = 2.0
```

### **Proxy Rotation**
Enable automatic proxy rotation:
```python
use_proxy: bool = True
proxy_rotation: bool = True
```

## 🛠️ Development

### **Project Structure**
```
├── main_modern.py          # Main entry point
├── main_window.py          # Main window and tabs
├── modern_ui.py           # UI components
├── request_bot.py         # Request-based bot
├── worker_thread.py       # Background workers
├── modern_config.py       # Configuration
├── modern_utils.py        # Utilities
└── requirements_modern.txt # Dependencies
```

### **Adding New Features**
1. Create new UI components in `modern_ui.py`
2. Add bot logic in `request_bot.py`
3. Create worker threads in `worker_thread.py`
4. Update configuration in `modern_config.py`

## 🐛 Troubleshooting

### **Common Issues**

1. **Connection Errors**
   - Check internet connection
   - Verify proxy settings
   - Test connection button

2. **Account Creation Fails**
   - Check email:password format
   - Verify email accounts are valid
   - Check proxy rotation

3. **UI Issues**
   - Update PyQt6: `pip install --upgrade PyQt6`
   - Check system compatibility

### **Debug Mode**
Enable debug logging:
```python
import logging
logging.basicConfig(level=logging.DEBUG)
```

## 📈 Performance Tips

### **Optimization**
1. **Use Proxies**: Distribute requests across multiple IPs
2. **Adjust Concurrency**: Increase for faster processing
3. **Optimize Timeouts**: Reduce for faster failures
4. **Use Strong Passwords**: Reduce account rejection

### **Best Practices**
1. **Test Connection**: Always test before bulk creation
2. **Monitor Progress**: Watch for errors and adjust
3. **Export Results**: Save successful accounts immediately
4. **Use Valid Emails**: Ensure email accounts are accessible

## 🔒 Security

### **Data Protection**
- No sensitive data stored locally
- Secure request handling
- Proxy support for anonymity

### **Rate Limiting**
- Built-in request delays
- Configurable retry logic
- Respectful API usage

## 📝 Changelog

### v3.0.0 (Latest)
- Complete rewrite with PyQt6
- Request-based architecture
- Async processing
- Modern UI design
- Advanced proxy support
- Real-time progress tracking

### v2.0.0
- Modern Python practices
- Improved error handling
- Better configuration management

### v1.0.0
- Initial release
- Basic functionality

## 🤝 Support

- **Issues**: Report bugs and feature requests
- **Documentation**: Check this README
- **Updates**: Regular updates and improvements

## ⚠️ Disclaimer

This tool is for educational purposes only. Users are responsible for:
- Complying with all applicable laws
- Respecting terms of service
- Using responsibly and ethically

## 📄 License

This project requires a valid license. Contact the author for licensing information.

---

**Made with ❤️ by mebularts**