pipeline {
    agent any
    
    environment {
        BACKEND_IMAGE = 'emavra-backend'
        FRONTEND_IMAGE = 'emavra-frontend'
        GIT_REPO = 'https://tu-repositorio-git.com/emavra.git'
    }
    
    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', 
                    url: "${GIT_REPO}",
                    credentialsId: 'git-credentials'
            }
        }
        
        stage('Build Backend') {
            steps {
                dir('emavra-backend') {
                    sh 'docker build -t ${BACKEND_IMAGE}:latest .'
                }
            }
        }
        
        stage('Build Frontend') {
            steps {
                dir('frontend') {
                    sh 'docker build -t ${FRONTEND_IMAGE}:latest --build-arg REACT_APP_API_URL=http://servidor-backend:8001/api .'
                }
            }
        }
        
        stage('Run Migrations') {
            steps {
                sh 'docker exec emavra_backend php artisan migrate --force'
            }
        }
        
        stage('Deploy') {
            steps {
                sh '''
                    docker-compose down
                    docker-compose up -d --build
                '''
            }
        }
    }
    
    post {
        success {
            echo '✅ Deployment exitoso!'
        }
        failure {
            echo '❌ Deployment falló!'
        }
    }
}