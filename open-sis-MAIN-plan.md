# OpenSIS Classic - Complete Deployment Plan
## From Local Development to Google Cloud Run

---

## Project Overview
**Goal**: Deploy OpenSIS Classic (Student Information System) from local development to production on Google Cloud Run with a public URL.

**Current Status**: 
- [ ] Local setup pending
- [ ] GitHub repository not created
- [ ] Cloud deployment not started

**Project Location**: `C:\Users\c_clo\OneDrive\Personal\Coding\OpenSIS\openSIS-Classic-master`

---

## Phase 1: Local Development Setup with XAMPP

### Step 1.1: Install XAMPP
**Objective**: Get Apache, MySQL, and PHP running locally

- [ ] Download XAMPP from https://www.apachefriends.org/
  - Choose Windows version
  - Ensure PHP 8.x is included (OpenSIS requires PHP 8.x)
- [ ] Run installer with default settings
  - Default installation path: `C:\xampp`
  - Components needed: Apache, MySQL, PHP, phpMyAdmin
- [ ] Open XAMPP Control Panel after installation
  - Location: `C:\xampp\xampp-control.exe`
  - Create desktop shortcut for easy access

### Step 1.2: Configure Apache to Serve from Current Directory
**Objective**: Keep project files in current location while serving through XAMPP

#### Option A: Using Apache Alias (Recommended - Simpler)
- [ ] Navigate to `C:\xampp\apache\conf\extra\`
- [ ] Create new file: `opensis.conf`
- [ ] Add the following configuration:
  ```apache
  Alias /opensis "C:/Users/c_clo/OneDrive/Personal/Coding/OpenSIS/openSIS-Classic-master"
  <Directory "C:/Users/c_clo/OneDrive/Personal/Coding/OpenSIS/openSIS-Classic-master">
      Options Indexes FollowSymLinks
      AllowOverride All
      Require all granted
      DirectoryIndex index.php
  </Directory>
  ```
- [ ] Open `C:\xampp\apache\conf\httpd.conf`
- [ ] Add at the bottom: `Include conf/extra/opensis.conf`
- [ ] Save the file

#### Option B: Using Virtual Host (More Professional)
- [ ] Open `C:\xampp\apache\conf\httpd.conf`
- [ ] Uncomment line: `Include conf/extra/httpd-vhosts.conf`
- [ ] Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
- [ ] Add virtual host configuration:
  ```apache
  <VirtualHost *:80>
      DocumentRoot "C:/Users/c_clo/OneDrive/Personal/Coding/OpenSIS/openSIS-Classic-master"
      ServerName opensis.local
      <Directory "C:/Users/c_clo/OneDrive/Personal/Coding/OpenSIS/openSIS-Classic-master">
          Options Indexes FollowSymLinks
          AllowOverride All
          Require all granted
      </Directory>
  </VirtualHost>
  ```
- [ ] Edit `C:\Windows\System32\drivers\etc\hosts` (as Administrator)
- [ ] Add line: `127.0.0.1 opensis.local`

### Step 1.3: Start XAMPP Services
- [ ] Open XAMPP Control Panel
- [ ] Click "Start" for Apache
  - Should show green background and port numbers (80, 443)
  - If port 80 is in use, change it in httpd.conf
- [ ] Click "Start" for MySQL
  - Should show green background and port 3306
  - If port 3306 is in use, change it in my.ini
- [ ] Test Apache: Navigate to http://localhost
  - Should see XAMPP welcome page

### Step 1.4: Create Database
- [ ] Open phpMyAdmin: http://localhost/phpmyadmin
  - Default login: username = root, password = (empty)
- [ ] Click "New" in left sidebar
- [ ] Create database:
  - Database name: `opensis`
  - Collation: `utf8_general_ci`
  - Click "Create"
- [ ] Note database credentials for later:
  - Host: localhost
  - Database: opensis
  - Username: root
  - Password: (empty)

### Step 1.5: Run OpenSIS Installation Wizard
- [ ] Navigate to installation URL:
  - If using Alias: http://localhost/opensis/install/
  - If using Virtual Host: http://opensis.local/install/
- [ ] Step 0: Welcome Screen
  - Click "New Installation"
- [ ] Step 1: System Requirements Check
  - Verify all items show green checkmarks
  - PHP version 8.x ✓
  - MySQL extension ✓
  - GD Library ✓
  - Required folders writable ✓
- [ ] Step 2: Database Connection
  - Server Name: `localhost`
  - Database Name: `opensis`
  - Username: `root`
  - Password: (leave empty for XAMPP default)
  - Click "Test Connection"
  - Click "Save & Next"
- [ ] Step 3: Database Setup
  - Choose "Install Fresh Database"
  - Select "Install with Sample Data" (recommended for testing)
  - Click "Save & Next"
- [ ] Step 4: School Information
  - School Name: [Your Test School Name]
  - Address details
  - Principal information
  - Click "Save & Next"
- [ ] Step 5: Admin Account Setup
  - Username: `admin`
  - Password: [choose secure password]
  - Email: [your email]
  - Remember these credentials!
  - Click "Save & Next"
- [ ] Installation Complete
  - Note the login URL
  - Data.php file will be created automatically

### Step 1.6: Test Local Installation
- [ ] Access main application:
  - URL: http://localhost/opensis/ (or http://opensis.local/)
- [ ] Login with admin credentials
- [ ] Test key features:
  - [ ] Navigate to School Setup
  - [ ] Check Students module
  - [ ] Check Users module
  - [ ] Test Scheduling
  - [ ] Verify Attendance module
  - [ ] Test Grades/Gradebook
  - [ ] Check Reports generation
- [ ] Create test data:
  - [ ] Add a test student
  - [ ] Add a test teacher
  - [ ] Create a test course
  - [ ] Test enrollment process

### Step 1.7: Troubleshooting Local Setup
Common Issues and Solutions:

**Apache won't start:**
- Port 80 in use: Change to 8080 in httpd.conf
- Skype using port 80: Disable Skype's port 80 usage
- Windows IIS running: Stop IIS service

**MySQL won't start:**
- Port 3306 in use: Change port in my.ini
- Another MySQL instance running: Stop other instance

**Installation wizard errors:**
- Can't connect to database: Verify MySQL is running
- Permission errors: Ensure Apache has write permissions
- Blank page: Check PHP error logs in `C:\xampp\apache\logs\error.log`

---

## Phase 2: Prepare for Cloud Deployment

### Step 2.1: Create Configuration for Production
- [ ] Create `config.production.php` file
- [ ] Structure for environment-based configuration:
  ```php
  <?php
  // Production configuration
  $db_host = getenv('DB_HOST') ?: 'localhost';
  $db_name = getenv('DB_NAME') ?: 'opensis';
  $db_user = getenv('DB_USER') ?: 'root';
  $db_pass = getenv('DB_PASSWORD') ?: '';
  ?>
  ```
- [ ] Modify existing configuration files to use environment variables

### Step 2.2: Create Dockerfile
- [ ] Create `Dockerfile` in project root:
  ```dockerfile
  # Use PHP with Apache
  FROM php:8.1-apache
  
  # Install PHP extensions required by OpenSIS
  RUN apt-get update && apt-get install -y \
      libpng-dev \
      libjpeg-dev \
      libfreetype6-dev \
      zip \
      unzip \
      && docker-php-ext-configure gd --with-freetype --with-jpeg \
      && docker-php-ext-install gd mysqli pdo pdo_mysql
  
  # Enable Apache mod_rewrite
  RUN a2enmod rewrite
  
  # Copy application files
  COPY . /var/www/html/
  
  # Set permissions
  RUN chown -R www-data:www-data /var/www/html \
      && chmod -R 755 /var/www/html
  
  # Configure Apache
  RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
  
  # Expose port 80
  EXPOSE 80
  
  # Start Apache
  CMD ["apache2-foreground"]
  ```

### Step 2.3: Create docker-compose.yml for Local Docker Testing
- [ ] Create `docker-compose.yml`:
  ```yaml
  version: '3.8'
  
  services:
    web:
      build: .
      ports:
        - "8080:80"
      environment:
        - DB_HOST=db
        - DB_NAME=opensis
        - DB_USER=root
        - DB_PASSWORD=rootpassword
      depends_on:
        - db
      volumes:
        - ./:/var/www/html
    
    db:
      image: mysql:8.0
      environment:
        - MYSQL_ROOT_PASSWORD=rootpassword
        - MYSQL_DATABASE=opensis
      ports:
        - "3307:3306"
      volumes:
        - mysql_data:/var/lib/mysql
  
  volumes:
    mysql_data:
  ```

### Step 2.4: Test Docker Setup Locally
- [ ] Install Docker Desktop for Windows
- [ ] Build Docker image: `docker-compose build`
- [ ] Start containers: `docker-compose up -d`
- [ ] Test application at http://localhost:8080
- [ ] Run installation wizard in Docker environment
- [ ] Stop containers: `docker-compose down`

### Step 2.5: Create .gitignore File
- [ ] Create `.gitignore` in project root:
  ```
  # Configuration files with sensitive data
  Data.php
  config.local.php
  
  # Temporary files
  *.tmp
  *.temp
  *.cache
  
  # Log files
  *.log
  logs/
  
  # Upload directories (user content)
  assets/studentphotos/*
  assets/userphotos/*
  assets/schoollogo/*
  !assets/studentphotos/.gitkeep
  !assets/userphotos/.gitkeep
  !assets/schoollogo/.gitkeep
  
  # OS files
  .DS_Store
  Thumbs.db
  
  # IDE files
  .vscode/
  .idea/
  *.swp
  *.swo
  
  # Docker volumes
  mysql_data/
  
  # Backup files
  *.backup
  *.bak
  backup/
  ```

### Step 2.6: Create Environment Configuration Template
- [ ] Create `Data.php.example`:
  ```php
  <?php
  // Database configuration template
  // Copy this file to Data.php and update with your values
  
  $DatabaseType = 'mysql';
  $DatabaseServer = 'localhost';
  $DatabaseUsername = 'root';
  $DatabasePassword = '';
  $DatabaseName = 'opensis';
  $DatabasePort = '3306';
  
  // Production: Use environment variables
  // $DatabaseServer = $_ENV['DB_HOST'];
  // $DatabaseUsername = $_ENV['DB_USER'];
  // $DatabasePassword = $_ENV['DB_PASSWORD'];
  // $DatabaseName = $_ENV['DB_NAME'];
  ?>
  ```

---

## Phase 3: GitHub Repository Setup

### Step 3.1: Initialize Git Repository
- [ ] Open terminal in project directory
- [ ] Initialize git: `git init`
- [ ] Add all files: `git add .`
- [ ] Create initial commit: `git commit -m "Initial commit - OpenSIS Classic"`

### Step 3.2: Create GitHub Repository
- [ ] Login to GitHub (https://github.com)
- [ ] Click "New repository"
- [ ] Repository settings:
  - Name: `opensis-classic`
  - Description: "OpenSIS Classic Student Information System"
  - Visibility: Private (initially)
  - Do NOT initialize with README (we already have one)
- [ ] Copy repository URL

### Step 3.3: Push to GitHub
- [ ] Add remote origin:
  ```bash
  git remote add origin https://github.com/[yourusername]/opensis-classic.git
  ```
- [ ] Push to main branch:
  ```bash
  git branch -M main
  git push -u origin main
  ```
- [ ] Verify files appear on GitHub

### Step 3.4: Create GitHub Actions Workflow (Optional)
- [ ] Create `.github/workflows/deploy.yml`:
  ```yaml
  name: Deploy to Google Cloud Run
  
  on:
    push:
      branches: [ main ]
  
  env:
    PROJECT_ID: your-gcp-project-id
    SERVICE_NAME: opensis
    REGION: us-central1
  
  jobs:
    deploy:
      runs-on: ubuntu-latest
      
      steps:
      - uses: actions/checkout@v2
      
      - uses: google-github-actions/setup-gcloud@v0
        with:
          service_account_key: ${{ secrets.GCP_SA_KEY }}
          project_id: ${{ env.PROJECT_ID }}
      
      - name: Build and Push Container
        run: |
          gcloud builds submit --tag gcr.io/$PROJECT_ID/$SERVICE_NAME
      
      - name: Deploy to Cloud Run
        run: |
          gcloud run deploy $SERVICE_NAME \
            --image gcr.io/$PROJECT_ID/$SERVICE_NAME \
            --platform managed \
            --region $REGION \
            --allow-unauthenticated
  ```

---

## Phase 4: Google Cloud Setup

### Step 4.1: Google Cloud Project Setup
- [ ] Go to https://console.cloud.google.com
- [ ] Create new project or select existing
- [ ] Note Project ID: `your-project-id`
- [ ] Enable required APIs:
  - [ ] Cloud Run API
  - [ ] Cloud Build API
  - [ ] Container Registry API
  - [ ] Cloud SQL Admin API
  - [ ] Secret Manager API

### Step 4.2: Install Google Cloud SDK
- [ ] Download from https://cloud.google.com/sdk/docs/install
- [ ] Run installer for Windows
- [ ] Initialize gcloud: `gcloud init`
- [ ] Authenticate: `gcloud auth login`
- [ ] Set project: `gcloud config set project your-project-id`

### Step 4.3: Create Cloud SQL Instance
- [ ] Create MySQL instance:
  ```bash
  gcloud sql instances create opensis-mysql \
    --database-version=MYSQL_8_0 \
    --tier=db-f1-micro \
    --region=us-central1
  ```
- [ ] Set root password:
  ```bash
  gcloud sql users set-password root \
    --instance=opensis-mysql \
    --password=YourSecurePassword
  ```
- [ ] Create database:
  ```bash
  gcloud sql databases create opensis \
    --instance=opensis-mysql
  ```
- [ ] Note connection name: `project-id:region:opensis-mysql`

### Step 4.4: Import Database Schema
- [ ] Export local database:
  ```bash
  mysqldump -u root opensis > opensis_backup.sql
  ```
- [ ] Upload to Cloud Storage:
  ```bash
  gsutil mb gs://your-project-id-opensis-backup
  gsutil cp opensis_backup.sql gs://your-project-id-opensis-backup/
  ```
- [ ] Import to Cloud SQL:
  ```bash
  gcloud sql import sql opensis-mysql \
    gs://your-project-id-opensis-backup/opensis_backup.sql \
    --database=opensis
  ```

### Step 4.5: Configure Secrets
- [ ] Create secrets for database credentials:
  ```bash
  echo -n "project-id:region:opensis-mysql" | \
    gcloud secrets create db-host --data-file=-
  
  echo -n "opensis" | \
    gcloud secrets create db-name --data-file=-
  
  echo -n "root" | \
    gcloud secrets create db-user --data-file=-
  
  echo -n "YourSecurePassword" | \
    gcloud secrets create db-password --data-file=-
  ```

### Step 4.6: Build Container Image
- [ ] Create `cloudbuild.yaml`:
  ```yaml
  steps:
  # Build the container image
  - name: 'gcr.io/cloud-builders/docker'
    args: ['build', '-t', 'gcr.io/$PROJECT_ID/opensis', '.']
  
  # Push the container image to Container Registry
  - name: 'gcr.io/cloud-builders/docker'
    args: ['push', 'gcr.io/$PROJECT_ID/opensis']
  
  images:
  - 'gcr.io/$PROJECT_ID/opensis'
  ```
- [ ] Submit build:
  ```bash
  gcloud builds submit --config cloudbuild.yaml
  ```

### Step 4.7: Deploy to Cloud Run
- [ ] Deploy service:
  ```bash
  gcloud run deploy opensis \
    --image gcr.io/your-project-id/opensis \
    --platform managed \
    --region us-central1 \
    --allow-unauthenticated \
    --add-cloudsql-instances project-id:region:opensis-mysql \
    --set-env-vars DB_HOST=/cloudsql/project-id:region:opensis-mysql \
    --set-env-vars DB_NAME=opensis \
    --set-env-vars DB_USER=root \
    --set-secrets DB_PASSWORD=db-password:latest
  ```
- [ ] Note the service URL: `https://opensis-xxxxx-uc.a.run.app`

### Step 4.8: Configure Custom Domain (Optional)
- [ ] Verify domain ownership in Google Search Console
- [ ] Map domain in Cloud Run:
  ```bash
  gcloud run domain-mappings create \
    --service opensis \
    --domain yourdomain.com \
    --region us-central1
  ```
- [ ] Update DNS records as instructed

### Step 4.9: Configure Cloud Storage for File Uploads
- [ ] Create storage bucket:
  ```bash
  gsutil mb gs://your-project-id-opensis-files
  ```
- [ ] Set permissions:
  ```bash
  gsutil iam ch allUsers:objectViewer gs://your-project-id-opensis-files
  ```
- [ ] Update application to use Cloud Storage for uploads

---

## Phase 5: Production Configuration

### Step 5.1: Security Hardening
- [ ] Enable HTTPS only
- [ ] Configure Cloud Armor for DDoS protection
- [ ] Set up Cloud IAP for admin access
- [ ] Configure backup strategy
- [ ] Enable audit logging

### Step 5.2: Performance Optimization
- [ ] Configure Cloud CDN for static assets
- [ ] Set up Cloud Memorystore for caching
- [ ] Configure autoscaling parameters
- [ ] Optimize container size

### Step 5.3: Monitoring Setup
- [ ] Configure Cloud Monitoring alerts
- [ ] Set up uptime checks
- [ ] Configure error reporting
- [ ] Set up log aggregation

### Step 5.4: Backup Strategy
- [ ] Schedule automatic Cloud SQL backups
- [ ] Configure point-in-time recovery
- [ ] Test restore procedures
- [ ] Document recovery process

---

## Verification Checklist

### Local Testing Complete
- [ ] Application runs locally via XAMPP
- [ ] All modules functional
- [ ] Sample data loaded
- [ ] Admin account working

### Docker Testing Complete
- [ ] Docker image builds successfully
- [ ] Container runs locally
- [ ] Database connection works
- [ ] Application accessible via browser

### GitHub Repository Ready
- [ ] Code pushed to repository
- [ ] .gitignore properly configured
- [ ] Sensitive data excluded
- [ ] README updated

### Google Cloud Deployment Complete
- [ ] Cloud SQL instance running
- [ ] Database imported
- [ ] Cloud Run service deployed
- [ ] Public URL accessible
- [ ] Custom domain configured (optional)

### Production Ready
- [ ] Security measures in place
- [ ] Monitoring configured
- [ ] Backup strategy implemented
- [ ] Documentation complete

---

## Troubleshooting Guide

### Common Local Issues

**Problem**: XAMPP Apache won't start
- **Solution**: Check port conflicts, disable Skype/IIS

**Problem**: Database connection failed
- **Solution**: Verify MySQL is running, check credentials

**Problem**: Blank white page
- **Solution**: Check PHP error logs, verify file permissions

### Common Docker Issues

**Problem**: Container won't build
- **Solution**: Check Dockerfile syntax, verify base image

**Problem**: Database connection fails in Docker
- **Solution**: Check network configuration, verify environment variables

### Common Cloud Run Issues

**Problem**: Cloud SQL connection fails
- **Solution**: Verify Cloud SQL proxy configuration, check IAM permissions

**Problem**: Application crashes on Cloud Run
- **Solution**: Check memory limits, review logs in Cloud Console

**Problem**: File uploads don't work
- **Solution**: Configure Cloud Storage, update file paths in code

---

## Important URLs and Resources

### Documentation
- OpenSIS Documentation: https://www.os4ed.com/
- XAMPP Documentation: https://www.apachefriends.org/faq_windows.html
- Docker Documentation: https://docs.docker.com/
- Google Cloud Run: https://cloud.google.com/run/docs

### Your Project URLs (Update these)
- Local Development: http://localhost/opensis/
- Docker Local: http://localhost:8080
- GitHub Repository: https://github.com/[yourusername]/opensis-classic
- Cloud Run URL: https://opensis-xxxxx-uc.a.run.app
- Custom Domain: https://your-domain.com (if configured)

### Support Resources
- OpenSIS Community: https://www.os4ed.com/
- Google Cloud Support: https://cloud.google.com/support
- Stack Overflow Tags: #opensis #google-cloud-run #php

---

## Notes Section
Use this section to track specific issues, customizations, or important information:

### Custom Modifications Made:
- 

### Known Issues:
- 

### Important Passwords/Credentials (Store Securely!):
- Local MySQL: root / (empty)
- Cloud SQL: root / [set during setup]
- Admin Account: admin / [set during setup]

### Next Steps After Deployment:
1. Configure email settings for notifications
2. Set up regular backups
3. Train users on the system
4. Customize school branding
5. Import actual student/staff data

---

## Status Log
Track your progress here with dates:

- Project Started: [Date]
- Local Setup Completed: [Date]
- Docker Testing Completed: [Date]
- GitHub Repository Created: [Date]
- Cloud Deployment Completed: [Date]
- Production Launch: [Date]

---

## IMPORTANT: Understanding Local vs Production Deployment

### Local Development (What We Just Did)
**Purpose**: Development and testing only
**Tools Used**:
- **XAMPP**: Provides Apache web server + MySQL database locally
- **phpMyAdmin**: Web interface to manage local MySQL database
- **Local URL**: http://localhost/opensis/

**These tools are ONLY for local development - NOT needed in production!**

### Production Deployment (Google Cloud Run)
**Purpose**: Live system accessible from internet
**Architecture**:
```
Users (Internet) 
    ↓
Google Cloud Run (PHP Application)
    ↓
Google Cloud SQL (MySQL Database)
    ↓
Google Cloud Storage (File Uploads)
```

### Where Data Lives in Production

| Component | Local Development | Production (Cloud) |
|-----------|------------------|--------------------|
| PHP Code | Your computer folder | Cloud Run container |
| Database | XAMPP MySQL | Cloud SQL (managed MySQL) |
| Student Data | Local MySQL | Cloud SQL (persistent) |
| Uploaded Files | Local folder | Cloud Storage bucket |
| Access URL | http://localhost/opensis/ | https://schoolname.run.app |

**Key Point**: Cloud Run is stateless - it only runs code. All data is stored in Cloud SQL (database) and Cloud Storage (files).

---

## Multi-School Deployment Strategy

### Option 1: Separate Instances (RECOMMENDED)
Best for data isolation, security, and customization

```
School A:
├── Cloud Run: school-a-opensis
├── Cloud SQL: school-a-db
├── URL: https://school-a.run.app
└── Complete isolation from other schools

School B:
├── Cloud Run: school-b-opensis
├── Cloud SQL: school-b-db
├── URL: https://school-b.run.app
└── Complete isolation from other schools
```

**Advantages**:
- Complete data isolation
- Independent updates/maintenance
- Custom domains easy to set up
- Clear billing per school
- No risk of data leaks between schools

**Cost**: ~$25-100/month per school

### Option 2: Multi-Tenant (Single Instance)
All schools share one instance - more complex but cheaper

```
All Schools:
├── One Cloud Run instance
├── One Cloud SQL (data separated by school_id)
├── Subdomains or paths (school-a.app, school-b.app)
└── Shared resources
```

**Not recommended for beginners** - requires code modifications

---

## Deployment Process for New Client Schools

### First School (Demo/Test) - What We're Doing Now
1. ✅ Set up local development with XAMPP
2. ✅ Test everything locally
3. Create Docker configuration
4. Push to GitHub
5. Deploy to Cloud Run
6. Connect Cloud SQL
7. Result: https://opensis-demo.run.app

### Each Additional School - Simplified Process

**Prerequisites**: 
- Your Docker image already in Google Container Registry
- Your code already in GitHub

**Steps for New School**:

#### 1. Create Cloud SQL Instance for the School
```bash
# Create database for new school
gcloud sql instances create school-name-mysql \
  --database-version=MYSQL_8_0 \
  --tier=db-f1-micro \
  --region=us-central1

# Create database
gcloud sql databases create opensis \
  --instance=school-name-mysql

# Set password
gcloud sql users set-password root \
  --instance=school-name-mysql \
  --password=SchoolSpecificPassword
```

#### 2. Deploy Cloud Run Service
```bash
# Deploy the application
gcloud run deploy school-name-opensis \
  --image gcr.io/your-project/opensis \
  --add-cloudsql-instances your-project:us-central1:school-name-mysql \
  --set-env-vars DB_HOST=/cloudsql/your-project:us-central1:school-name-mysql \
  --set-env-vars DB_NAME=opensis \
  --set-env-vars DB_USER=root \
  --set-secrets DB_PASSWORD=school-name-db-password:latest \
  --region us-central1 \
  --allow-unauthenticated
```

#### 3. Client Sets Up Their School
1. School admin goes to: `https://school-name-opensis.run.app/install/`
2. Runs installation wizard in their browser
3. Enters:
   - School name
   - School year dates
   - Admin account details
4. System creates all database tables automatically
5. School is ready to use!

#### 4. Optional: Custom Domain
```bash
# Map their domain
gcloud run domain-mappings create \
  --service school-name-opensis \
  --domain their-school.edu \
  --region us-central1
```

**No XAMPP, No phpMyAdmin, No technical setup needed by the school!**

---

## Quick Reference: Deployment Commands for New Schools

### Complete New School Setup Script
```bash
#!/bin/bash
SCHOOL_NAME="new-school"
PROJECT_ID="your-project-id"
REGION="us-central1"

# 1. Create Cloud SQL
gcloud sql instances create ${SCHOOL_NAME}-mysql \
  --database-version=MYSQL_8_0 \
  --tier=db-f1-micro \
  --region=${REGION}

# 2. Create database
gcloud sql databases create opensis \
  --instance=${SCHOOL_NAME}-mysql

# 3. Set password
gcloud sql users set-password root \
  --instance=${SCHOOL_NAME}-mysql \
  --password=GeneratedSecurePassword

# 4. Store password in Secret Manager
echo -n "GeneratedSecurePassword" | \
  gcloud secrets create ${SCHOOL_NAME}-db-password --data-file=-

# 5. Deploy Cloud Run
gcloud run deploy ${SCHOOL_NAME}-opensis \
  --image gcr.io/${PROJECT_ID}/opensis \
  --add-cloudsql-instances ${PROJECT_ID}:${REGION}:${SCHOOL_NAME}-mysql \
  --set-env-vars DB_HOST=/cloudsql/${PROJECT_ID}:${REGION}:${SCHOOL_NAME}-mysql \
  --set-env-vars DB_NAME=opensis \
  --set-env-vars DB_USER=root \
  --set-secrets DB_PASSWORD=${SCHOOL_NAME}-db-password:latest \
  --region ${REGION} \
  --allow-unauthenticated

# 6. Get the URL
echo "School URL: $(gcloud run services describe ${SCHOOL_NAME}-opensis --region ${REGION} --format 'value(status.url)')"
```

---

## Cost Breakdown Per School

### Small School (< 500 students)
- **Cloud Run**: ~$10-20/month
- **Cloud SQL** (db-f1-micro): ~$15/month  
- **Cloud Storage**: ~$5/month
- **Total**: ~$30-40/month

### Medium School (500-2000 students)
- **Cloud Run**: ~$30-50/month
- **Cloud SQL** (db-n1-standard-1): ~$50/month
- **Cloud Storage**: ~$10/month
- **Total**: ~$90-110/month

### Large School (2000+ students)
- **Cloud Run**: ~$50-100/month
- **Cloud SQL** (db-n1-standard-2): ~$100/month
- **Cloud Storage**: ~$20/month
- **Total**: ~$170-220/month

---

## Troubleshooting Production Deployments

### Common Issues and Solutions

**Issue**: Installation wizard can't connect to database
- **Solution**: Check Cloud SQL proxy connection in Cloud Run
- Verify environment variables are set correctly
- Ensure Cloud SQL Admin API is enabled

**Issue**: File uploads not working
- **Solution**: Configure Cloud Storage bucket
- Update code to use Cloud Storage instead of local filesystem
- Set proper IAM permissions

**Issue**: Slow performance
- **Solution**: Increase Cloud Run memory/CPU
- Upgrade Cloud SQL tier
- Enable Cloud CDN for static assets

**Issue**: Can't access after deployment
- **Solution**: Check Cloud Run logs
- Verify --allow-unauthenticated flag
- Check firewall rules

---

## Important Notes for Production

### Security Considerations
1. **Never** commit database passwords to GitHub
2. Use Secret Manager for all sensitive data
3. Enable Cloud SQL backups
4. Use HTTPS only (Cloud Run provides this automatically)
5. Set up Cloud IAP for admin-only access areas

### Maintenance
1. **Backups**: Configure automatic Cloud SQL backups
2. **Updates**: Deploy updates during off-hours
3. **Monitoring**: Set up alerts for downtime
4. **Logs**: Regular review Cloud Run and Cloud SQL logs

### Data Compliance
- Ensure compliance with educational data regulations (FERPA, etc.)
- Keep each school's data completely isolated
- Regular security audits
- Document data retention policies

---

*Last Updated: [Current Date]*
*Plan Version: 1.1 - Added Production Deployment Guide*