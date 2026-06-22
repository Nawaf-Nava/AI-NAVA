
    import subprocess
    print(subprocess.check_output(['sudo', 'apt-get', 'update']).decode())
    print(subprocess.check_output(['sudo', 'apt-get', 'install', '-y', 'build-essential', 'python3-dev']).decode())
    