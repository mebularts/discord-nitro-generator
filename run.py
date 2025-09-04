#!/usr/bin/env python3
"""
Quick launcher for Modern Mackolik Request Bot
"""
import sys
import os
from pathlib import Path

# Add current directory to Python path
current_dir = Path(__file__).parent
sys.path.insert(0, str(current_dir))

# Import and run main
from main_modern import main_sync

if __name__ == '__main__':
    main_sync()