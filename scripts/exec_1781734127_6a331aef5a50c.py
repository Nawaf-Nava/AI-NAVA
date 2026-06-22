
        import subprocess
        print(subprocess.check_output(['ls', '-la', '/path/to/resource']).decode())
        print(subprocess.check_output(['id']).decode())
        