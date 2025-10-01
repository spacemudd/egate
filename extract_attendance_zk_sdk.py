#!/usr/bin/env python3
"""
ZKTeco Attendance Data Extractor
Uses the ZKTeco binary protocol (port 4370) to retrieve attendance data directly from the device.
This is the CORRECT method to retrieve historical attendance data.
"""

import sys
import csv
from datetime import datetime, timedelta

try:
    from zk import ZK
    from zk.exception import ZKErrorResponse, ZKNetworkError
except ImportError:
    print("ERROR: pyzk library not installed!")
    print("Install it with: pip3 install pyzk")
    sys.exit(1)

# Device configuration
DEVICE_IP = '91.73.75.13'  # Device SYZ8252100929
DEVICE_PORT = 4370
TIMEOUT = 10

def extract_attendance_data():
    """Connect to ZKTeco device and extract all attendance records."""
    
    print("=" * 70)
    print("ZKTeco Attendance Data Extractor")
    print("=" * 70)
    print(f"Device IP: {DEVICE_IP}")
    print(f"Device Port: {DEVICE_PORT}")
    print()
    
    # Create ZK instance
    # Try with ommit_ping=True since device responds to HTTP but not ICMP
    zk = ZK(DEVICE_IP, port=DEVICE_PORT, timeout=TIMEOUT, password=0, force_udp=False, ommit_ping=True)
    conn = None
    
    try:
        print("[1/5] Connecting to device...")
        conn = zk.connect()
        print("✓ Connected successfully!")
        print()
        
        # Get device info
        print("[2/5] Retrieving device information...")
        firmware = conn.get_firmware_version()
        serialnumber = conn.get_serialnumber()
        platform = conn.get_platform()
        device_name = conn.get_device_name()
        
        print(f"  Firmware Version: {firmware}")
        print(f"  Serial Number: {serialnumber}")
        print(f"  Platform: {platform}")
        print(f"  Device Name: {device_name}")
        print()
        
        # Get user information
        print("[3/5] Retrieving user information...")
        users = conn.get_users()
        print(f"✓ Found {len(users)} users")
        
        # Create user ID to name mapping
        user_map = {}
        for user in users:
            user_map[user.user_id] = {
                'name': user.name,
                'privilege': user.privilege,
                'card': user.card,
            }
        
        # Print user list
        print("\nUser List:")
        print("-" * 70)
        for user_id, info in sorted(user_map.items()):
            print(f"  User ID {user_id:6s}: {info['name']}")
        print()
        
        # Get attendance records
        print("[4/5] Retrieving attendance records...")
        attendances = conn.get_attendance()
        print(f"✓ Found {len(attendances)} attendance records")
        print()
        
        # Filter for last month
        one_month_ago = datetime.now() - timedelta(days=30)
        filtered_attendances = [
            att for att in attendances
            if att.timestamp >= one_month_ago
        ]
        
        print(f"✓ {len(filtered_attendances)} records from last 30 days")
        print()
        
        # Save to CSV
        print("[5/5] Saving to CSV file...")
        csv_filename = 'attendance_data_complete.csv'
        
        with open(csv_filename, 'w', newline='') as csvfile:
            fieldnames = ['User ID', 'User Name', 'Date', 'Time', 'Status', 'Punch Type', 'Full Timestamp']
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            
            writer.writeheader()
            
            for att in sorted(filtered_attendances, key=lambda x: x.timestamp):
                user_id = att.user_id
                user_name = user_map.get(user_id, {}).get('name', 'Unknown')
                timestamp = att.timestamp
                date_str = timestamp.strftime('%Y-%m-%d')
                time_str = timestamp.strftime('%H:%M:%S')
                full_timestamp = timestamp.strftime('%Y-%m-%d %H:%M:%S')
                status = att.status
                punch = att.punch
                
                writer.writerow({
                    'User ID': user_id,
                    'User Name': user_name,
                    'Date': date_str,
                    'Time': time_str,
                    'Status': status,
                    'Punch Type': punch,
                    'Full Timestamp': full_timestamp
                })
        
        print(f"✓ Data saved to: {csv_filename}")
        print()
        
        # Print summary
        print("=" * 70)
        print("SUMMARY")
        print("=" * 70)
        print(f"Total Users: {len(users)}")
        print(f"Total Attendance Records: {len(attendances)}")
        print(f"Records (Last 30 Days): {len(filtered_attendances)}")
        print()
        
        # Count by user
        user_counts = {}
        for att in filtered_attendances:
            user_id = att.user_id
            user_counts[user_id] = user_counts.get(user_id, 0) + 1
        
        print("Attendance by User (Last 30 Days):")
        print("-" * 70)
        for user_id in sorted(user_counts.keys()):
            user_name = user_map.get(user_id, {}).get('name', 'Unknown')
            count = user_counts[user_id]
            print(f"  User {user_id:6s} ({user_name:20s}): {count:3d} punches")
        
        print()
        print("✓ SUCCESS! All data extracted successfully.")
        print()
        
    except ZKNetworkError as e:
        print(f"✗ Network Error: {e}")
        print("\nPossible causes:")
        print("  1. Device IP address is incorrect")
        print("  2. Device is not accessible from this server")
        print("  3. Firewall blocking port 4370")
        print("  4. Device is offline")
        return False
        
    except ZKErrorResponse as e:
        print(f"✗ Device Error: {e}")
        print("\nPossible causes:")
        print("  1. Device password is incorrect")
        print("  2. Device firmware doesn't support this command")
        print("  3. Device is in wrong mode (check Access Control vs Time Attendance)")
        return False
        
    except Exception as e:
        print(f"✗ Unexpected Error: {e}")
        import traceback
        traceback.print_exc()
        return False
        
    finally:
        if conn:
            conn.disconnect()
            print("[Disconnected from device]")
    
    return True

if __name__ == '__main__':
    success = extract_attendance_data()
    sys.exit(0 if success else 1)

