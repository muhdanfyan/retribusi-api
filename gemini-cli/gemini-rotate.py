#!/usr/bin/env python3
import os
import sys
import json
import requests

# Path to the keys file
KEYS_FILE = os.path.expanduser("~/.gemini_keys")

def load_keys():
    if not os.path.exists(KEYS_FILE):
        print(f"Error: Keys file not found at {KEYS_FILE}")
        sys.exit(1)
    
    with open(KEYS_FILE, 'r') as f:
        keys = [line.strip() for line in f if line.strip() and not line.startswith('#')]
    return keys

def call_gemini(prompt, api_key):
    url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={api_key}"
    headers = {'Content-Type': 'application/json'}
    data = {
        "contents": [{
            "parts": [{"text": prompt}]
        }]
    }
    
    try:
        response = requests.post(url, headers=headers, json=data, timeout=30)
        return response
    except Exception as e:
        return None

def main():
    if len(sys.argv) < 2:
        print("Usage: gemini \"your prompt here\"")
        sys.exit(1)
    
    prompt = sys.argv[1]
    keys = load_keys()
    
    if not keys:
        print("Error: No API keys found in configuration.")
        sys.exit(1)
    
    for i, key in enumerate(keys):
        # Determine if this is the ultimate fallback
        is_fallback = (i == len(keys) - 1)
        
        response = call_gemini(prompt, key)
        
        if response is None:
            print(f"Warning: Connection error with key {i+1}. Rotating...")
            continue
            
        if response.status_code == 200:
            try:
                result = response.json()
                print(result['candidates'][0]['content']['parts'][0]['text'])
                return
            except (KeyError, IndexError):
                print(f"Error: Unexpected response format from key {i+1}.")
                continue
        elif response.status_code == 429:
            if not is_fallback:
                # print(f"Warning: Key {i+1} rate limited. Rotating to next key...")
                continue
            else:
                print("Error: All keys, including ultimate fallback, are rate limited.")
        else:
            try:
                err_msg = response.json().get('error', {}).get('message', 'Unknown error')
                if not is_fallback:
                    # print(f"Warning: Key {i+1} failed ({err_msg}). Rotating...")
                    continue
                else:
                    print(f"Error: Ultimate fallback key failed: {err_msg}")
            except:
                print(f"Error: Key {i+1} failed with status {response.status_code}.")
                continue

    print("Error: Successfully exhausted all available API keys.")
    sys.exit(1)

if __name__ == "__main__":
    main()
