pipeline {
    agent any

    stages {
        stage('Test'){
            steps {
                sh 'docker run --rm -w /opt/data -v `pwd`:/opt/data -i epcallan/php7-testing-phpunit:7.0-phpunit6 composer install'
                sh 'docker run --rm -w /opt/data -v `pwd`:/opt/data -i epcallan/php7-testing-phpunit:7.0-phpunit6 phpunit --coverage-clover=phpunit/coverage.xml --log-junit phpunit/junit.xml -c Tests/Builds/UnitTests.xml'
                withCredentials([string(credentialsId: 'CODECOV_TOKEN', variable: 'CODECOV_TOKEN')]) {
                    sh 'ci_env=`bash -c "bash <(curl -s https://codecov.io/env)"` && docker run --rm $ci_env -w /opt/data -v `pwd`:/opt/data -i epcallan/php7-testing-phpunit:7.0-phpunit6 bash -c "bash <(curl -s https://codecov.io/bash)"'
                }
            }
        }
        stage('Deploy'){
            when {
                expression {
                    env.TAG_NAME ==~ /^[0-9]+[.][0-9]+[.][0-9]+$/
                }
            }
            steps {
                withCredentials([string(credentialsId: 'GITHUB_TOKEN', variable: 'GITHUB_TOKEN')]) {
                    sh 'git pull --tags'
                    sh 'docker run --rm -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make checkVersion'
                    sh 'docker run --rm -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make release'
                }
            }
        }
        stage('Notify') {
            steps {
                slackSend color: 'good', message: 'Building sn_webservice: Done'
            }
        }

    }
}
