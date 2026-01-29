#!/bin/bash
timestamp=$(date +%s)
public_id="pbb"
folder="retribusi/mobile/icons"
api_key="684916711959441"
api_secret="J8OsO7AxNTFG1jbSFk1t1RZ2t0s"
cloud_name="ddhgtgsed"

string_to_sign="folder=$folder&public_id=$public_id&timestamp=$timestamp$api_secret"
signature=$(echo -n "$string_to_sign" | shasum -a 1 | awk '{print $1}')

echo "Uploading to $cloud_name..."
curl -v -X POST "https://api.cloudinary.com/v1_1/$cloud_name/image/upload" \
     -F "file=@/Users/pondokit/.gemini/antigravity/brain/849f30b2-f6e4-4642-b00f-f1f897da9048/pbb_icon_1769705570029.png" \
     -F "folder=$folder" \
     -F "public_id=$public_id" \
     -F "timestamp=$timestamp" \
     -F "api_key=$api_key" \
     -F "signature=$signature"
