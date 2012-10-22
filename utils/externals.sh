#!/bin/bash
#call: script.sh production

PRODUCTION_MODE=0
ADDONS_PATH="/opt" #for production ,override in no production mode

if [ "$1" = "production" ]
then
    PRODUCTION_MODE=1
    # Make sure only root can run our script
    if [[ $EUID -ne 0 ]]; then
        echo "This script must be run as root" 1>&2
        exit 1
    fi
fi

########## FUNCTIONS #################
function development {
    ADDONS_PATH=$(pwd)
    mkdir $ADDONS_PATH/externals/
    cd $ADDONS_PATH/externals/

    #zend framework
    #svn co http://framework.zend.com/svn/framework/standard/tags/release-1.12.0/library/ zend
    ZF_RELEASE='ZendFramework-1.12.0'
    wget "http://packages.zendframework.com/releases/$ZF_RELEASE/$ZF_RELEASE-minimal.tar.gz"
    tar zxvf "$ZF_RELEASE-minimal.tar.gz"
    mv "$ZF_RELEASE-minimal" zend
    rm "$ZF_RELEASE-minimal.tar.gz"

    #no como submodulo porque el eclipse empaquetado en ubuntu 12.04 no los maneja apropiadamente
    #php testmore - usado para probar el cliente php de openerp
    git clone https://github.com/shiflett/testmore.git testmore-php
}

function production {
    echo "no procedure defined";
}

#####################################################
#####################################################

if [ "$PRODUCTION_MODE" == 0 ]
then
     development
else
     production
fi
