
import subprocess
import sys

try:
    subprocess.check_call([sys.executable, "-m", "pip", "install", "pynput"])
    print("pynput installed successfully.")
except subprocess.CalledProcessError as e:
    print(f"Failed to install pynput: {e}")
