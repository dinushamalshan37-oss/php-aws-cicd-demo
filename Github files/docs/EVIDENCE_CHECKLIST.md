# Deployment Evidence Checklist (GitHub variant)

Keep dated screenshots of the following:

- [ ] GitHub repository home showing `main` and project files
- [ ] `.github/workflows/deploy.yml` visible in repository
- [ ] AWS EC2 instance details (hide sensitive details)
- [ ] `sudo -u deploy /usr/local/bin/deploy.sh` manual deployment output
- [ ] GitHub Actions run showing successful PHP validation and deployment
- [ ] `git -C /var/www/myapp log -1 --oneline` showing matching commit
- [ ] Browser showing the PHP site served by EC2
- [ ] A deliberately caused **safe** failure in a disposable environment and its explanation
- [ ] Written rollback plan including code, database and uploads

Note that the provided lab specifies **GitLab CI/CD**. GitHub Actions evidence may not satisfy that precise rubric.
