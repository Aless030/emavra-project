pipeline {
    agent any
    
    environment {
        BACKEND_IMAGE = 'emavra-backend-dev'
        FRONTEND_IMAGE = 'emavra-frontend-dev'
        GIT_REPO = 'https://github.com/Aless030/emavra-project.git'
        COMPOSE_FILE = 'docker-compose-dev.yml'
    }
    
    stages {
        stage('Checkout') {
            steps {
                git branch: 'dev', 
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
                    sh '''
                        docker build -t ${FRONTEND_IMAGE}:latest \
                        --build-arg REACT_APP_API_URL=http://192.168.104.201:8001/api \
                        --build-arg REACT_APP_STORAGE_URL=http://192.168.104.201:8001/storage \
                        --build-arg REACT_APP_MAPBOX_TOKEN=pk.eyJ1IjoiYWxlc3NpcyIsImEiOiJjbGcxbHBtbHQwdDU5M2RubDFodjY3a2x0In0.NXe43GdM4PJBj7ow0Dnkpw \
                        .
                    '''
                }
            }
        }
        
        stage('Run Migrations') {
            steps {
                sh 'docker exec emavra-backend-dev php artisan migrate --force'
            }
        }
        
        stage('Deploy') {
            steps {
                sh '''
                    docker-compose -f ${COMPOSE_FILE} down
                    docker-compose -f ${COMPOSE_FILE} up -d --build
                '''
            }
        }
    }
    
    post {
        success {
            echo '✅ Deployment DEV exitoso!'
        }
        failure {
            echo '❌ Deployment DEV falló!'
        }
    }
}