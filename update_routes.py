import base64
import json
import urllib.request
import sys

token = sys.argv[1]
headers = {
    'Authorization': 'token ' + token,
    'Accept': 'application/vnd.github.v3+json',
    'Content-Type': 'application/json'
}

files = [
    {
        'local_path': r'C:\xampp\htdocs\ubereats pro\6amMart v3.8 Nulled\Admin panel new install V3.8\routes\admin.php',
        'repo_path': 'routes/admin.php',
        'sha': '12c47d4eb201a3e50af231003c9c1fe2cf7b04a2'
    },
    {
        'local_path': r'C:\xampp\htdocs\ubereats pro\6amMart v3.8 Nulled\Admin panel new install V3.8\routes\admin\routes.php',
        'repo_path': 'routes/admin/routes.php',
        'sha': '9ecdc70b15bd4b8e9288cf7b0ec014a287e26321'
    }
]

for f in files:
    with open(f['local_path'], 'rb') as file:
        content = base64.b64encode(file.read()).decode('utf-8')
    
    data = json.dumps({
        'message': 'fix: rename duplicate route names causing route:cache failure',
        'content': content,
        'sha': f['sha'],
        'branch': 'master'
    }).encode('utf-8')
    
    url = 'https://api.github.com/repos/miguel913111/ubereats-pro/contents/' + f['repo_path']
    req = urllib.request.Request(url, data=data, headers=headers, method='PUT')
    
    try:
        with urllib.request.urlopen(req) as response:
            print(f['repo_path'], 'OK', response.status)
    except urllib.error.HTTPError as e:
        print(f['repo_path'], 'ERROR', e.code, e.read().decode('utf-8')[:500])
