# GitHub Actions → AWS EC2 → Core PHP

A portfolio-friendly CI/CD lab: pushing to the `main` branch triggers a GitHub Actions workflow, checks PHP syntax, and invokes a **server-side** deployment script over SSH. EC2 fetches the current `main` branch from GitHub; Apache serves the PHP site.

> **Coursework note:** This is a **GitHub Actions adaptation**, not an implementation of the lab's required **GitLab CI/CD**. If GitLab is mandatory for grading, also submit and demonstrate the GitLab version.

## Structure

```text
.
├── .github/workflows/deploy.yml  # automated PHP check + SSH deployment
├── .gitignore                    # excludes common credentials and runtime files
├── index.php                     # sample Core PHP website
├── deploy.sh                     # install on EC2 as /usr/local/bin/deploy.sh
└── docs/
    ├── SETUP_GUIDE.md           # safe beginner step-by-step setup
    ├── ROLLBACK_PLAN.md          # limitations and a basic rollback plan
    └── EVIDENCE_CHECKLIST.md     # screenshots/evidence for the lab
```

## Quick start

1. Create an empty **GitHub** repository (e.g. `php-aws-cicd-demo`) with `main` as default branch.
2. Push the extracted **files and folders** (not the ZIP as a single repository file).
3. Follow [docs/SETUP_GUIDE.md](docs/SETUP_GUIDE.md) to create and configure Amazon Linux EC2, Apache, deploy user, and **two separate SSH keypairs**.
4. In GitHub **Settings → Secrets and variables → Actions**:
   - **Secrets**: `DEPLOY_SSH_PRIVATE_KEY` (CI→EC2 private key), `EC2_SSH_KNOWN_HOSTS` (verified server host key line)
   - **Variables**: `DEPLOY_HOST` (server IP or DNS), `DEPLOY_USER` (`deploy`)
5. Push a benign change to `main` and observe **Actions → Deploy PHP to EC2**. Verify the deployed commit on the server and the website in a browser.

## Access and security

- **EC2 → GitHub:** A read-only GitHub **deploy key**, with its private part kept only on EC2.
- **GitHub Actions → EC2:** A **different** SSH key, private part stored in GitHub Actions secrets and public part in `/home/deploy/.ssh/authorized_keys`.
- Verify both servers' SSH host fingerprints before writing `known_hosts` entries; do not disable StrictHostKeyChecking.
- Do **not** commit private keys, `.env`, user uploads, or server-side logs.
- Default GitHub-hosted runners have changing outbound IPs. Never permanently open EC2 SSH port 22 to the internet merely to make the workflow pass. Use appropriately restricted network access or a suitable runner/network design.
- `git reset --hard` overwrites any uncommitted tracked file edits on EC2. It is not a database, upload, or full environment rollback.
- AWS charges can apply. Destroy unused resources after your demonstration.

**Status:** source/config template only; no GitHub repository or AWS instance has been created for you. Secrets, trusted host keys, repository address, EC2 access, and deployment verification must be configured before Actions can succeed.
