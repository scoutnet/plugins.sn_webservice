pipeline {
    agent any

    stages {
        stage('Test'){
            steps {
                sh 'docker run --rm -v `pwd`:/app -i phpunit/phpunit --coverage-clover=./phpunit/coverage.xml --log-junit ./phpunit/junit.xml -c Tests/Builds/UnitTests.xml'
            }
        }
        stage('Build'){
            steps {
                sh "echo test"
            }
        }
        /*
        stage('Deploy'){
            when {
                expression {
                    env.TAG_NAME ==~ /^[0-9]+[.][0-9]+[.][0-9]+$/
                }
            }
            steps {
                withCredentials([string(credentialsId: 'GITHUB_TOKEN', variable: 'GITHUB_TOKEN'), usernamePassword(credentialsId: 'ac854e35-e62e-4aa1-b7ac-2ced736da9e6', passwordVariable: 'TYPO3_TER_PASSWORD', usernameVariable: 'TYPO3_TER_USER')]) {
                    sh 'git pull --tags'
                    sh 'docker run --rm -e TYPO3_TER_PASSWORD -e TYPO3_TER_USER -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make checkVersion'
                    sh 'docker run --rm -e TYPO3_TER_PASSWORD -e TYPO3_TER_USER -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make release'
                    sh 'docker run --rm -e TYPO3_TER_PASSWORD -e TYPO3_TER_USER -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make deploy'
                }
            }
        }
        */
        stage('Notify') {
            steps {
                slackSend color: 'good', message: 'Building sn_webservice: Done'
            }
        }

    }
}
