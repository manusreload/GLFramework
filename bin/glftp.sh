#!/usr/bin/env bash


SCRIPTS=~/.glftp
if [ ! -d ${SCRIPTS} ]; then
    mkdir ${SCRIPTS}
fi
FILE=${SCRIPTS}/${1}.sh
if [ ! -e ${FILE} ]; then
    EXAMPLE=${FILE}.example
    touch ${EXAMPLE}
    printf "FTP_HOST=\"127.0.0.1\" \\n\
FTP_USER=\"username\" \n\
FTP_PASSWORD=\"password\" \n\
FTP_PATH=\"/web\" \n\
\n\
WEB_HOST=\"http://demo.example.com\" \n\
DEPLOY_TOKEN=\"dQ2JWQOycGlRFq0xY1oRYnW9uyPqLQCd\"\n" > ${EXAMPLE}

    echo "Please provide the source file in ${FILE}. I'm already created a template called ${EXAMPLE},
    feel free to edit the config. Finally you can rename the file to ${FILE} to use these configuration."
    exit
fi
source "${SCRIPTS}/${1}.sh"


curl "${WEB_HOST}/scripts/toggle_maintenance.php?token=${DEPLOY_TOKEN}&mode=1"

git config git-ftp.user "${FTP_USER}"
git config git-ftp.password "${FTP_PASSWORD}"
git config git-ftp.url "${FTP_HOST}/$FTP_PATH"
git ftp push ${2}

## Despues ejecutar el install.php:

curl "${WEB_HOST}/install.php?exec"
curl "${WEB_HOST}/scripts/toggle_maintenance.php?token=${DEPLOY_TOKEN}&mode=0"
