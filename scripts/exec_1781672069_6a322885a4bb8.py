
import socket
import sys
from datetime import datetime
from concurrent.futures import ThreadPoolExecutor

# إعدادات الهدف والمنافذ
TARGET = "y.net"
# قائمة بالمنافذ الشائعة لفحصها (أو يمكنك فحص النطاق كامل من 1 إلى 65535)
COMMON_PORTS = [
    21, 22, 23, 25, 53, 80, 110, 135, 139, 143, 443, 445, 
    1433, 3306, 3389, 8080, 8291, 8443
]

def resolve_target(host):
    """تحويل النطاق إلى عنوان IP"""
    try:
        ip = socket.gethostbyname(host)
        print(f"[*] Target resolved: {host} -> {ip}")
        return ip
    except socket.gaierror:
        print(f"[-] Error: Cannot resolve host '{host}'")
        sys.exit(1)

def scan_port(ip, port):
    """فحص منفذ فردي باستخدام اتصال TCP Connect"""
    try:
        # إنشاء Socket مع تحديد مهلة زمنية قصيرة لتفادي التعليق
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(1.5)
        
        # محاولة الاتصال بالمنفذ
        result = s.connect_ex((ip, port))
        
        if result == 0:
            # محاولة جلب لافتة الخدمة (Banner Grabbing) إن أمكن
            try:
                # إرسال طلب فارغ لجلب معلومات الخدمة
                s.send(b"HEAD / HTTP/1.1\r\n\r\n")
                banner = s.recv(1024).decode('utf-8', errors='ignore').strip().split('\n')[0]
            except:
                banner = "Unknown Service"
            
            print(f"[+] Port {port:<5} [OPEN]  | Service: {socket.getservbyname(port, 'tcp') if port in [80,443,21,22,23] else 'Alternative'} | Banner: {banner}")
            s.close()
            return port, "OPEN", banner
        s.close()
    except Exception as e:
        pass
    return port, "CLOSED", ""

def run_scanner():
    ip = resolve_target(TARGET)
    
    print("-" * 60)
    print(f" NAVA [DEEP_CORE] - TCP Port Scanner Initiated")
    print(f" Target: {TARGET} ({ip})")
    print(f" Time Started: {datetime.now()}")
    print("-" * 60)
    
    open_ports = []
    
    # استخدام ThreadPoolExecutor لتسريع العملية عبر تعدد الخيوط
    with ThreadPoolExecutor(max_workers=50) as executor:
        futures = [executor.submit(scan_port, ip, port) for port in COMMON_PORTS]
        for future in futures:
            result = future.result()
            if result and result[1] == "OPEN":
                open_ports.append(result)
                
    print("-" * 60)
    print(f"[*] Scan Complete. Total Open Ports Found: {len(open_ports)}")
    print("-" * 60)

if __name__ == "__main__":
    run_scanner()
