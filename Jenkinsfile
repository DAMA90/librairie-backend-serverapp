pipeline {
    agent any

    environment {
        GIT_REPO = "https://github.com/DAMA90/librairie-backend-serverapp.git"
        GIT_BRANCH = "main"
        DEPLOY_DIR = "web010"
        DEPLOY_PATH = "/var/www/html/${DEPLOY_DIR}"
    }

    stages {
        stage('Cloner le dépôt') {
            steps {
                sh "rm -rf ${DEPLOY_DIR}" // Nettoyage du précédent build
                sh "git clone -b ${GIT_BRANCH} ${GIT_REPO} ${DEPLOY_DIR}"
                sh "echo $(date) > ${DEPLOY_DIR}/force_build" // Forcer la reconstruction
            }
        }

        stage('Installation des dépendances') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --no-dev --optimize-autoloader'
                }
            }
        }

        stage('Configuration de l\'environnement') {
            steps {
                script {
                    def envLocal = """
                    APP_ENV=prod
                    APP_DEBUG=0
                    DATABASE_URL=mysql://root:@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4
                    """.stripIndent()

                    writeFile file: "${DEPLOY_DIR}/.env.local", text: envLocal
                }
            }
        }

        stage('Migration de la base de données') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=prod || true'
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true'
                }
            }
        }

        stage('Nettoyage du cache') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console cache:warmup'
                }
            }
        }

        stage('Déploiement') {
            steps {
                sh "sudo rm -rf ${DEPLOY_PATH}" // Supprime le dossier de destination
                sh "sudo mkdir -p ${DEPLOY_PATH}" // Crée le dossier s'il n'existe pas
                sh "sudo cp -rT ${DEPLOY_DIR} ${DEPLOY_PATH}"
                sh "sudo chmod -R 775 ${DEPLOY_PATH}/var"
                sh "sudo chown -R www-data:www-data ${DEPLOY_PATH}" // Assurer que le serveur web a les droits
            }
        }
    }

    post {
        success {
            echo '🚀 Déploiement réussi !'
        }
        failure {
            echo '❌ Erreur lors du déploiement.'
        }
    }
}
