# invisiblescribe-site

One repo = one domain (invisiblescribe.com). Deploy via Hostinger Git auto-deploy on push to `main`.

## Structure (CONVENTION — see hostinger-deploy skill)
- Repo root  = apex `invisiblescribe.com`  (index.html + .htaccess)
- `fintech/` = subdomain `fintech.invisiblescribe.com`  ("The Fintech Founder Authority Playbook" 5-day EEC)
- Each new subdomain = a new top-level folder named exactly the subdomain label, with its own index.html + .htaccess.

## Hostinger wiring (once)
- Connect THIS repo to the apex domain (deploys repo root -> apex docroot).
- For each subdomain: create it in hPanel, set its document root to the matching subfolder (e.g. .../invisiblescribe-site/fintech).
- After that, every `git push origin main` updates apex + ALL subdomains from this single repo.
