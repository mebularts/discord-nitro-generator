import sys
import requests
import string
import random
from PyQt5.QtWidgets import QApplication, QMainWindow, QLabel, QPushButton, QVBoxLayout, QWidget, QSpinBox
from PyQt5.QtCore import QTimer, Qt, QUrl
from PyQt5.QtGui import QDesktopServices

DISCORD_WEBHOOK_URL = "YOUR_DISCORD_WEBHOOK_URL"
DEVELOPER_URL = "https://mebularts.com.tr"

class DiscordBot(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Discord Bot")

        self.generated_code_count = 0
        self.tried_code_count = 0
        self.approved_code_count = 0

        self.central_widget = QWidget()
        self.setCentralWidget(self.central_widget)

        self.layout = QVBoxLayout()
        self.central_widget.setLayout(self.layout)

        self.generated_code_label = QLabel(f"Generated Code Count: {self.generated_code_count}")
        self.layout.addWidget(self.generated_code_label)

        self.tried_code_label = QLabel(f"Tried Code Count: {self.tried_code_count}")
        self.layout.addWidget(self.tried_code_label)

        self.approved_code_label = QLabel(f"Approved Code Count: {self.approved_code_count}")
        self.layout.addWidget(self.approved_code_label)
        
        self.space_label = QLabel(f"--------------------------------------------------------")
        self.layout.addWidget(self.space_label)
        
        self.code_length_label = QLabel(f"https://discord.gift/(code_length) STD: 16/18")
        self.layout.addWidget(self.code_length_label)
        
        self.code_length_input = QSpinBox()
        self.code_length_input.setMinimum(1)
        self.code_length_input.setMaximum(100)
        self.code_length_input.setValue(16)
        self.layout.addWidget(self.code_length_input)
        
        self.space_label = QLabel(f"--------------------------------------------------------")
        self.layout.addWidget(self.space_label)

        self.start_button = QPushButton("Start")
        self.start_button.clicked.connect(self.start)
        self.layout.addWidget(self.start_button)

        self.stop_button = QPushButton("Stop")
        self.stop_button.clicked.connect(self.stop)
        self.layout.addWidget(self.stop_button)

        self.developer_label = QLabel("<a href='{0}'>Developer: mebularts</a>".format(DEVELOPER_URL))
        self.developer_label.setTextFormat(Qt.RichText)
        self.developer_label.setTextInteractionFlags(Qt.TextBrowserInteraction)
        self.developer_label.setOpenExternalLinks(True)
        self.layout.addWidget(self.developer_label)

        self.timer = QTimer(self)
        self.timer.timeout.connect(self.generate_and_check_code)

    def start(self):
        self.generated_code_count = 0
        self.tried_code_count = 0
        self.timer.start(1000)  # Run every 1 second

    def stop(self):
        self.timer.stop()

    def generate_and_check_code(self):
        length = self.code_length_input.value()
        generated_code = self.generate_random_string(length)
        self.tried_code_count += 1
        self.generated_code_count += 1
        self.generated_code_label.setText(f"Generated Code Count: {self.generated_code_count}")
        self.tried_code_label.setText(f"Tried Code Count: {self.tried_code_count}")
        with open("generated.txt", "a") as f:
                f.write(generated_code + "\n")

        url = f"https://discordapp.com/api/v9/entitlements/gift-codes/{generated_code}?with_application=false&with_subscription_plan=true"
        response = requests.get(url)
        if response.status_code == 200:
            self.approved_code_count += 1
            self.approved_code_label.setText(f"Approved Code Count: {self.approved_code_count}")

            with open("approved.txt", "a") as f:
                f.write(generated_code + "\n")

            data = {"content": generated_code}
            requests.post(DISCORD_WEBHOOK_URL, json=data)

    def generate_random_string(self, length):
        characters = string.ascii_letters + string.digits
        return ''.join(random.choice(characters) for _ in range(length))

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = DiscordBot()
    window.show()
    sys.exit(app.exec_())
