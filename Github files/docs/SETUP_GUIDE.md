# Setup Guide: GitHub Actions + EC2 (Amazon Linux 2023)

**This is the GitHub adaptation.** The supplied school PDF explicitly asks for GitLab CI/CD. Use the original GitLab lab project to satisfy that exact requirement.

## 1. GitHub repo (on your own account)

1. Go to https://github.com/new and create a repo named `php-aws-cicd-demo`. Choose Private or Public; use default `main` branch.
2. Extract the ZIP and upload **all extracted files/folders** to the repository. Include the hidden `.github/workflows/deploy.yml` folder (Windows Explorer may hide dotfiles). For a new repo, you can push using Git:
   ```bash
   git init
   git branch -M main
   git add .
   git commit -m "Initial PHP AWS CI/CD demo"
   git remote add origin https://github.com/YOUR_GITHUB_USERNAME/php-aws-cicd-demo.git
   git push -u origin main
   ```
   If your repository already exists with a README commit, clone it first and copy these files into it instead.
3. The workflow will trigger but initially **fail** until you have finished the EC2 and secret configuration. This is expected.

## 2. EC2 instance and network

1. In AWS, launch a small **Amazon Linux 2023** instance suitable for your account and workload. Download the admin `.pem` key securely; AWS may charge for EC2 and network resources.
2. Allow HTTP/TCP `80` to visitors (for the demo) and SSH/TCP `22` from your **own IP** for administrative setup. Do not permanently allow TCP 22 from `0.0.0.0/0`.
3. GitHub-hosted runners have dynamically allocated public IPs; their direct SSH connection to an IP-restricted EC2 instance may fail. The secure solutions are a dedicated self-hosted runner in an appropriately controlled network or a runner/network option with a fixed, narrowly allowed source IP. Do not weaken your security group just to make the sample pass.
4. Connect with `ssh -i /path/to/aws-admin-key.pem ec2-user@YOUR_EC2_PUBLIC_IP`.

Install services:
```bash
sudo dnf install -y git openssh-clients httpd php php-fpm
sudo systemctl enable --now httpd
sudo systemctl enable --now php-fpm
sudo useradd -m -s /bin/bash deploy
sudo mkdir -p /var/www/myapp
sudo chown -R deploy:apache /var/www/myapp
sudo chmod 750 /var/www/myapp
```
If the `deploy` user already exists, do not repeat `useradd`.

Configure Apache root:
```bash
sudo tee /etc/httpd/conf.d/myapp.conf > /dev/null <<'EOF'
<VirtualHost *:80>
  DocumentRoot /var/www/myapp
  <Directory /var/www/myapp>
    Require all granted
    AllowOverride None
  </Directory>
</VirtualHost>
EOF
sudo apachectl configtest
sudo systemctl restart httpd
```
Check Apache DocumentRoot and SELinux labeling if HTTP shows `403`.

## 3. SSH key A: EC2 → GitHub (read-only)

Run **on EC2**, with the `deploy` account:
```bash
sudo -iu deploy
mkdir -p ~/.ssh
chmod 700 ~/.ssh
ssh-keygen -t ed25519 -C "ec2-github-readonly" -f ~/.ssh/github_repo
cat > ~/.ssh/config <<'EOF'
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/github_repo
    IdentitiesOnly yes
EOF
chmod 600 ~/.ssh/config
cat ~/.ssh/github_repo.pub
```
Create a passphrase-less key **only for this unattended lab deployment** and protect it with file permissions. Copy **only** the `.pub` key to GitHub repo → **Settings → Deploy keys → Add deploy key**, leaving **Allow write access** unchecked.

Verify GitHub.com's SSH host fingerprint against https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/githubs-ssh-key-fingerprints before storing it in `/home/deploy/.ssh/known_hosts`. Do not blindly trust an unverified `ssh-keyscan` result. Check:
```bash
ssh -T git@github.com
# A successful authentication may exit with status 1 and a GitHub greeting; that is normal.
git ls-remote git@github.com:YOUR_GITHUB_USERNAME/YOUR_REPO.git
exit
```

## 4. Clone code & install deploy script

Run **on EC2** as admin (with real GitHub repo path):
```bash
sudo -u deploy git clone --branch main \
  git@github.com:YOUR_GITHUB_USERNAME/YOUR_REPO.git /var/www/myapp
```
The destination must be empty or non-existent, and `deploy` must own it. Edit `deploy.sh` (supplied in the ZIP) so its `REPO_URL` **exactly** matches `git -C /var/www/myapp remote get-url origin`. Then transfer it to the server, for example from your workstation:
```bash
scp -i /path/to/aws-admin-key.pem deploy.sh ec2-user@YOUR_EC2_PUBLIC_IP:/tmp/myapp-deploy.sh
```
On EC2:
```bash
sudo install -o root -g deploy -m 750 /tmp/myapp-deploy.sh /usr/local/bin/deploy.sh
sudo -u deploy /usr/local/bin/deploy.sh
sudo -u deploy git -C /var/www/myapp log -1 --oneline
```
Visit `http://YOUR_EC2_PUBLIC_IP` and check the PHP page displays.

## 5. SSH key B: GitHub Actions → EC2

Generate a **different** key on **your own computer** (not EC2):
```bash
ssh-keygen -t ed25519 -C "github-actions-to-ec2" -f github_actions_deploy
```
Copy the contents of **`github_actions_deploy.pub`** into `/home/deploy/.ssh/authorized_keys` on EC2; **append**, never overwrite existing keys. On EC2:
```bash
sudo install -d -m 700 -o deploy -g deploy /home/deploy/.ssh
sudo touch /home/deploy/.ssh/authorized_keys
sudo chown deploy:deploy /home/deploy/.ssh/authorized_keys
sudo chmod 600 /home/deploy/.ssh/authorized_keys
sudo nano /home/deploy/.ssh/authorized_keys
```
Test from your own computer:
```bash
ssh -i github_actions_deploy deploy@YOUR_EC2_PUBLIC_IP
```
Do not give unrestricted sudo to `deploy`.

## 6. GitHub secrets and variables

GitHub repo → **Settings → Secrets and variables → Actions**.

**Repository secrets**:

- `DEPLOY_SSH_PRIVATE_KEY`: contents of **`github_actions_deploy`**, including BEGIN/END lines, with a final newline.
- `EC2_SSH_KNOWN_HOSTS`: verified EC2 SSH host public key entry, with the **same IP or DNS name** used as `DEPLOY_HOST`. You may obtain the host key through an already trusted administrative EC2 session, e.g. `sudo cat /etc/ssh/ssh_host_ed25519_key.pub`, and construct the known_hosts line in format `YOUR_EC2_PUBLIC_IP ssh-ed25519 AAAA...`. Verify the fingerprint out of band.

**Repository variables**:

- `DEPLOY_HOST`: your EC2 public IP or DNS name
- `DEPLOY_USER`: `deploy`

`production` environment is referenced by the workflow. Configure branch/environment protections as needed. **Never post the private key in a chat or commit it to GitHub files.**

## 7. Test

1. After verifying network path from your chosen runner to EC2, edit `index.php` and commit to `main`.
2. Go to GitHub repo → **Actions → Deploy PHP to EC2**; the workflow should pass PHP syntax check then connect to EC2 using SSH.
3. Confirm the log shows `Deployed commit: ...`; on EC2 run:
   ```bash
   sudo -u deploy git -C /var/www/myapp log -1 --oneline
   sudo systemctl status httpd
   ```
4. Refresh the website. GitHub SHA and EC2 SHA should match.
5. For an intentional safe error demonstration, use a **disposable test environment** with an incorrect `REPO_URL`, record the failure, then restore the correct URL.

## 8. Troubleshooting

- `Permission denied (publickey)` at EC2 connection: CI→EC2 private/public key mismatch, wrong deploy username, or `.ssh` permissions.
- `Permission denied (publickey)` on `git fetch`: EC2→GitHub deploy key, repo Deploy keys settings, or github.com known_hosts entry.
- `Host key verification failed`: verify and correct `known_hosts`; never turn checking off.
- Connection timeout: ensure your runner can connect to restricted EC2 SSH, with network rules correctly scoped.
- `Repository URL does not match`: edit `REPO_URL` in `/usr/local/bin/deploy.sh` to match `git remote get-url origin` exactly.
- Apache 403: DocumentRoot, directory execute permissions, and SELinux context where applicable.
- `git reset --hard` deletes local tracked code edits; keep uploads and `.env` out of tracked files and back up the database separately.

References: https://docs.github.com/en/actions ; https://docs.github.com/en/authentication/connecting-to-github-with-ssh/managing-deploy-keys ; https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/connect-linux-inst-ssh.html
