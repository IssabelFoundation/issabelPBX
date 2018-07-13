Summary: IssabelPBX is a GUI configuration tool for Asterisk. Forked from FreePBX/AMP
Name: issabelPBX
Version: 2.11.0
Release: 46
License: GPL
Group: Applications/System
Source0: issabelpbx-%{version}.%{release}.tgz
Source1: issabelpbx-modules-%{version}.%{release}.tgz
Source2: issabelpbx-database-dump-%{version}.%{release}.sql
Source3: issabelpbx-rc.local
Source4: issabelpbx-fake-config.php
Source5: musiconhold_additional.conf
Source6: chan_dahdi.conf
Source7: issabelpbx.conf
Source8: issabelpbx-web-%{version}.%{release}.tgz
Source9: logger_general_additional.conf
Source10: logger_logfiles_additional.conf
Source11: issabel_issabelpbx_auth.php
Source12: issabel_advice.php

BuildRoot: %{_tmppath}/%{name}-%{version}.%{release}-root
BuildArch: noarch
Requires: asterisk >= 1.8, /sbin/pidof, /bin/tar, issabel-firstboot
Requires: php, php-pear-DB
Requires: gettext
Requires: issabel-framework >= 2.2.0-18
AutoReqProv: no
Obsoletes: freePBX
Provides: freePBX

%description
IssabelPBX is a GUI configuration tool for Asterisk. This project is forked from FreePBX/AMP

%prep
%setup -n issabelpbx
tar -xzf %{SOURCE8} -C amp_conf/htdocs/admin/

rm -f `find . -name *.orig`

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT/var/www/html/admin/modules

mkdir -p $RPM_BUILD_ROOT/etc/
mkdir -p $RPM_BUILD_ROOT/etc/rc.d/
mkdir -p $RPM_BUILD_ROOT/etc/asterisk.issabel/
mkdir -p $RPM_BUILD_ROOT/etc/pbx/
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/bin/
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/mohmp3/
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/sounds/
mkdir -p $RPM_BUILD_ROOT/usr/sbin/
mkdir -p $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/

#Fixed bug, module recordings missed
##mkdir -p $RPM_BUILD_ROOT/var/www/html/recordings/

#mkdir -p $RPM_BUILD_ROOT/etc/asterisk

# Copio las fuentes de IssabelPBX en la carpeta temporal pues serviran en caso de actualizacion
#cp %{SOURCE0} $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/issabelpbx-%{version}.tar.gz

# Copio los modulos adicionales de issabelpbx en la carpeta temporal, esto será utilizado en el
# caso de las actulizaciones para que todos los modulos queden de forma correcta.
#cp %{SOURCE1} $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/issabelpbx-modules-%{version}.4.tar.gz

# El parche 11,13 debe aplicarse también a los tar que se copian en /usr/share
mkdir temp
cd temp

# Se descomprime y parcha IssabelPBX principal
# Pienso que hasta este punto la directiva setup
# y los parches aplicados arriba ya se ejecutaron
# por lo tanto no veo necesario volver a descomptimir
# y parchar. Es solo de crear un tar comprimir
# y copiarlo a $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/

tar -xzf %{SOURCE0}
cd issabelpbx/
tar -xzf %{SOURCE8} -C amp_conf/htdocs/admin/

rm -f `find . -name *.orig`
cd ..
tar -czf issabelpbx-%{version}.%{release}.tgz issabelpbx/
mv issabelpbx-%{version}.%{release}.tgz $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/
rm -rf issabelpbx/

# Se descomprime y parcha módulos a instalar
mkdir temp2
cd temp2
tar -xzf %{SOURCE1}
rm -f `find . -name *.orig`
tar -czf ../issabelpbx-modules-%{version}.%{release}.tgz *
cd ..
rm -rf temp2
mv issabelpbx-modules-%{version}.%{release}.tgz $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/

cd ..
rmdir temp


# Copio los archivos binarios de mysql en una carpeta temporal para ser utilizados en el POST
# siempre y cuando se trate de una instalacion de Issabel.
cp %{SOURCE2} $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/

# Copying some agi scripts needed by issabelpbx
cp -ra $RPM_BUILD_DIR/issabelpbx/amp_conf/agi-bin/* $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/
## cp -ra $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/admin/modules/core/agi-bin/* $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/

# Copying some asterisk configuration files modified by issabelpbx
cp -ra $RPM_BUILD_DIR/issabelpbx/amp_conf/astetc/* $RPM_BUILD_ROOT/etc/asterisk.issabel/
cp %{SOURCE5} $RPM_BUILD_ROOT/etc/asterisk.issabel/
mv $RPM_BUILD_DIR/issabelpbx/asterisk.conf $RPM_BUILD_ROOT/etc/asterisk.issabel/

mv $RPM_BUILD_DIR/issabelpbx/amp_conf/bin/* $RPM_BUILD_ROOT/var/lib/asterisk/bin/

# Copying modules
mv $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/admin/modules/* $RPM_BUILD_ROOT/var/www/html/admin/modules/
#tar -xvzf %{SOURCE1} -C $RPM_BUILD_ROOT/var/www/html/admin/modules/
#Es mejor usar el issabelpbx-modules-*.tgz ya que esta parchado, %{SOURCE1} referencia a la fuente sin parches
tar -xvzf $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/issabelpbx-modules-%{version}.%{release}.tgz -C $RPM_BUILD_ROOT/var/www/html/admin/modules/

#mv $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/admin/modules/.htaccess $RPM_BUILD_ROOT/var/www/html/admin/modules/
rm -rf $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/admin/modules

cp %{SOURCE3} $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/rc.local

mv $RPM_BUILD_DIR/issabelpbx/amp_conf/sbin/* $RPM_BUILD_ROOT/usr/sbin/

mv $RPM_BUILD_DIR/issabelpbx/amp_conf/sounds/* $RPM_BUILD_ROOT/var/lib/asterisk/sounds/
mv $RPM_BUILD_DIR/issabelpbx/amportal.conf $RPM_BUILD_ROOT/etc/
chmod 644 $RPM_BUILD_ROOT/etc/amportal.conf
cp %{SOURCE4} $RPM_BUILD_ROOT/var/www/html/config.php

# Copying images
mv $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/admin/images $RPM_BUILD_ROOT/var/www/html/admin/

# FIXME: Maybe the following should be a slink
cp $RPM_BUILD_ROOT/var/www/html/admin/modules/dashboard/images/* $RPM_BUILD_ROOT/var/www/html/admin/images/
#Recently Commented 12/08/2013
#cp $RPM_BUILD_ROOT/var/www/html/admin/modules/recordings/images/* $RPM_BUILD_ROOT/var/www/html/admin/images/
#Fixed bug, module recordins missed. Recently Commented 12/08/2013


#echo "RECORDINGS MODULE INSTALL"
#rm -rf $RPM_BUILD_ROOT/var/www/html/recordings/
#cp -ra $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/recordings/  $RPM_BUILD_ROOT/var/www/html/
#cp -ra $RPM_BUILD_ROOT/var/www/html/recordings/theme/images/* $RPM_BUILD_ROOT/var/www/html/admin/images/


# Copying everything else
mv $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/admin/*           $RPM_BUILD_ROOT/var/www/html/admin/
mv $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/admin/.htaccess   $RPM_BUILD_ROOT/var/www/html/admin/

#put new zapata.conf custom of issabel. 
cp %{SOURCE6} $RPM_BUILD_ROOT/etc/asterisk.issabel/

# CDR dump as CSV
mkdir -p $RPM_BUILD_ROOT/var/log/asterisk/cdr-csv/
#chown -R asterisk.asterisk $RPM_BUILD_ROOT/var/log/asterisk/
touch $RPM_BUILD_ROOT/var/log/asterisk/cdr-csv/Master.csv

# IssabelPBX change the file /var/www/html/index.php (this is issabel file).
# File /etc/pbx/aver.flag is a flag for prevent it.
touch   $RPM_BUILD_ROOT/etc/pbx/aver.flag
rm -rf  $RPM_BUILD_DIR/issabelpbx/amp_conf/htdocs/index.php

# IssabelPBX file configure
cp %{SOURCE7} $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/

# The IssabelPBX logger.conf references additional files which must exist for ISO install
cp %{SOURCE9} %{SOURCE10} $RPM_BUILD_ROOT/etc/asterisk.issabel/

cp %{SOURCE11} $RPM_BUILD_ROOT/var/www/html/admin/
cp %{SOURCE12} $RPM_BUILD_ROOT/var/www/html/admin/views/

%pre
# Asegurarse de que este issabelPBX no sobreescriba una versión actualizada a mano
if [ $1 -eq 2 ] ; then
    if [ -f /var/www/html/admin/modules/framework/amp_conf/htdocs/index.php ]; then
        rm -f /var/www/html/admin/modules/framework/amp_conf/htdocs/index.php
    fi
    MYSQL_ROOTPWD=`grep mysqlrootpwd= /etc/issabel.conf | sed 's/^mysqlrootpwd=//'`
    ISSABELPBX_CURRVER=`echo "SELECT value FROM asterisk.admin WHERE variable = 'version';" | mysql -s -u root -p$MYSQL_ROOTPWD`
    mkdir -p $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/
    echo $ISSABELPBX_CURRVER > $RPM_BUILD_ROOT/usr/share/issabelpbx/tmp/issabelPBXver
    ISSABELPBX_NEWVER=%{version}.%{release}
    php -r "function c(\$a,\$b){while(count(\$a)>0&&count(\$b)>0){\$ax=array_shift(\$a);\$bx=array_shift(\$b);if(\$ax>\$bx)return 1;if(\$ax<\$bx)return -1;}if(count(\$a)>0)return 1;if(count(\$b)>0)return -1;return 0;}exit(c(explode('.', '$ISSABELPBX_CURRVER'), explode('.', '$ISSABELPBX_NEWVER'))>0?1:0);"
    res=$?
    if [ $res -eq 1 ] ; then
        echo "FATAL: tried to install IssabelPBX $ISSABELPBX_NEWVER, but system has IssabelPBX $ISSABELPBX_CURRVER"
        exit 1
    fi
fi

%post

echo "start post" >>/tmp/issabel_rpm.log

# Cambio contenido de "rc.local"
cat /usr/share/issabelpbx/tmp/rc.local > /etc/rc.d/rc.local
rm -f /usr/share/issabelpbx/tmp/rc.local
chmod 755 /etc/rc.d/rc.local

echo "rc.local modified" >>/tmp/issabel_rpm.log

if [ -d /var/www/html/admin/modules/fw_fop ]; then
    rm -rf /var/www/html/admin/modules/fw_fop
    echo "fw_fop removed" >>/tmp/issabel_rpm.log
fi

if [ -f /etc/issabel.conf ]; then
    echo "exists /etc/issabel.conf, check if we have /etc/issabelpbx.conf...." >>/tmp/issabel_rpm.log
    echo "Checking if issabelpbx was already installed ..."
    if [ ! -f /etc/issabelpbx.conf ] ; then
        echo "It was not installed, is IssabelPBX installed? ..." >>/tmp/issabel_rpm.log
        echo "It was not installed, is IssabelPBX installed? ..."
        if [ -f /etc/freepbx.conf ]; then
            echo "IssabelPBX is installed, so force module updates for IssabelPBX by removing modules and modules_xml table contents" >>/tmp/issabel_rpm.log
            echo "IssabelPBX is installed, so force module updates for IssabelPBX by removing modules and modules_xml table contents"
            # Force regeneration of modules table when upgrading from IssabelPBX
            issabel_root_password=`grep mysqlrootpwd= /etc/issabel.conf | sed 's/^mysqlrootpwd=//'`
            echo "root password for mysql is $issabel_root_password"
            final_mysql_password="-p$issabel_root_password"

            # Try to check if mysql has an empty root password
            mysql -e "select now()" >/dev/null
            if [ $? -eq 0 ]; then
                final_mysql_password='';
            fi

            echo "Truncating modules and module_xml tables"
            mysql -u root $final_mysql_password asterisk -e "TRUNCATE TABLE modules"
            mysql -u root $final_mysql_password asterisk -e "TRUNCATE TABLE module_xml"
        fi
    fi
fi

if [ $1 -eq 1 ] ; then # install
    echo "return of post is 1, this is an INSTALL" >>/tmp/issabel_rpm.log
    mv /usr/share/issabelpbx/tmp/issabelpbx.conf  /etc/
    chmod 660 /etc/issabelpbx.conf
    chown asterisk.asterisk /etc/issabelpbx.conf
    echo "Install on an existing system"

    # Este es el escenario en que la instalacion de IssabelPBX no es nueva
    # sino que se instala en un Issabel ya instalado.
    # TODO: Comentado porque aun falta ver como cambiar algunas claves de archivos como
    # amportal.conf, algunos de asterisk y la base de datos asterisk.
    # Pienso que es mejor ver como se llama a /usr/bin/issabel-admin-passwords --change --IssabelPBX --silent $AMI_ADMINPWD
    # de forma silenciosa para solo cambiar la parte de IssabelPBX y no MySQL. Tener en cuenta que la clave
    # recolectada de /etc/issabel.conf no este vacia, para hacer lo anterior mencionado.
    if [ -f /etc/issabel.conf ]; then
       echo "exists /etc/issabel.conf so change the password of issabelpbx.conf" >>/tmp/issabel_rpm.log

       AMI_ADMINPWD=`grep amiadminpwd= /etc/issabel.conf | sed 's/^amiadminpwd=//'`

       echo "/etc/issabel.conf found with AMI password $AMI_ADMINPWD!"
         
       sed -i -e "s/^\$amp_conf\['AMPDBPASS'\]\s*=\s*'\w*'/\$amp_conf['AMPDBPASS']  = '$AMI_ADMINPWD'/" /etc/issabelpbx.conf
    fi
else
    echo "Es un UPGRADE de RPM, revisar si manager.conf tiene el #include manager_general_additional.conf" >>/tmp/issabel_rpm.log

    grep manager_general_additional /etc/asterisk/manager.conf >/dev/null
    if [ $? -ne 0   ]; then
        echo "manager.conf no incluye el include dek general_additional, debo agregarlo"  >>//tmp/issabel_rpm.log
        sed -i '/^displayconnects/a #include manager_general_additional.conf' /etc/asterisk/manager.conf
        sed -i '/^displayconnects/d' /etc/asterisk/manager.conf
        if [ ! -f /etc/asterisk/manager_general_additional.conf  ]; then
            touch /etc/asterisk/manager_general_additional.conf
            echo "displayconnects=yes" >/etc/asterisk/manager_general_additional.conf
            echo "timestampevents=yes" >>/etc/asterisk/manager_general_additional.conf
            echo "webenabled=no" >>/etc/asterisk/manager_general_additional.conf
            chown asterisk.asterisk /etc/asterisk/manager_general_additional.conf
        fi
    else
        echo "ya lo tiene, no hago nada"
    fi
fi

# La base de datos esta corriendo
if [ x`pidof mysqld` != "x" ] ; then
    echo "mysql esta corriendo" >>/tmp/issabel_rpm.log
    # La base de datos existe
    # NOTA: Es muy importante notar que si la db esta corriendo y la base 'asterisk' existe no necesariamente
    #       tendria q ejecutar el script ./install_amp porque no necesariamente quiero actualizar
    echo "MySQL is running!"

    if [ -d "/var/lib/mysql/asterisk" ]; then
        # Procedimiento de actualizacion aqui?
        # Por ahora no hago nada pero creo que se deberia invocar al instalador aqui install_amp
        echo "Installing IssabelPBX... "
        echo "Installing IssabelPBX... existe la base asterisk, descomprimo el tgz de issabelpbx" >>/tmp/issabel_rpm.log
        tar -xvzf /usr/share/issabelpbx/tmp/issabelpbx-%{version}.%{release}.tgz -C /usr/share/issabelpbx/tmp/

        # IssabelPBX change the file /var/www/html/index.php (this is issabel file).
        # File /etc/pbx/aver.flag is a flag for prevent it.
        mkdir -p /etc/pbx/
        touch    /etc/pbx/aver.flag
        rm -rf   /usr/share/issabelpbx/tmp/issabelpbx/amp_conf/htdocs/index.php
        echo "touch /etc/pbx/aver.flag and remove /usr/share/issabelpbx/tmp/issabelpbx/amp_conf/htdocs/index.php" >>/tmp/issabel_rpm.log

        # IssabelPBX su script install_amp con la opcion --force-version sobrescribe todos los archivos
        # que esten en la carpeta ../amp_conf/astetc/* como el archivo extensions_custom.conf es creado
        # por parches (no es default de tgz de IssabelPBX) el script lo reemplaza esto sucede en la libreria
        # libissabelpbx.install.php alrededor de la linea 315. El error que provocaba esto es que el 
        # archivo de configuracion extensions_custom.conf no debe ser modificado por ningun proceso, este
        # solo puede modificarse manualmente por el usuario del servidor.
        rm -rf /usr/share/issabelpbx/tmp/issabelpbx/amp_conf/astetc/extensions_custom.conf
        echo "borro el extensions_custom del directorio extract de freepbx" >>/tmp/issabel_rpm.log

        # Se copia los archivos dentro de la carpeta amp_conf/htdocs/admin/modules/
        tar -xvzf /usr/share/issabelpbx/tmp/issabelpbx-modules-%{version}.%{release}.tgz -C /usr/share/issabelpbx/tmp/issabelpbx/amp_conf/htdocs/admin/modules/
        echo "extract de modules en amp_conf/htdcos/admin/modules" >>/tmp/issabel_rpm.log

        # Si existe un archivo cbmysql.conf se reemplaza
        if [ -f /etc/asterisk/cbmysql.conf ] ; then
            echo "1 Saving cbmysql.conf ...";
            mv /etc/asterisk/cbmysql.conf /etc/asterisk/cbmysql.conf.bak_%{name}-%{version}-%{release}
        fi

        cd /usr/share/issabelpbx/tmp/issabelpbx/

        ActVer_issabelPBX=$(cat /usr/share/issabelpbx/tmp/issabelPBXver)
        echo a | ./install_amp --force-version=$ActVer_issabelPBX
        rm -rf /usr/share/issabelpbx/tmp/issabelPBXver
        
        # Restaurar archivo cbmysql.conf previo
        if [ -f /etc/asterisk/cbmysql.conf.bak_%{name}-%{version}-%{release} ] ; then
            echo "1 Restoring cbmysql.conf ...";
            mv /etc/asterisk/cbmysql.conf.bak_%{name}-%{version}-%{release} /etc/asterisk/cbmysql.conf
        fi

        echo "1 ejecuto module admin para instalar timeconditions y customcontexts" >>/tmp/issabel_rpm.log
         /var/lib/asterisk/bin/module_admin install timeconditions
         /var/lib/asterisk/bin/module_admin install customcontexts
        echo "1 ejecuto module admin para instalar local" >>/tmp/issabel_rpm.log
         /var/lib/asterisk/bin/module_admin installlocal
        echo "1 ejecuto module admin para desinstalar sipstation e irc" >>/tmp/issabel_rpm.log
         /var/lib/asterisk/bin/module_admin delete sipstation
         /var/lib/asterisk/bin/module_admin delete irc


        # La base de datos NO existe
    else
        echo "2 no existe la base la creo con el dump" >>/tmp/issabel_rpm.log
        # Creo la base de datos
        # Este caso ocurre si el usuario ha desinstalado previamente el RPM de issabelPBX

        issabel_root_password=`grep mysqlrootpwd= /etc/issabel.conf | sed 's/^mysqlrootpwd=//'`

        final_mysql_password="-p$issabel_root_password"

        # Try to check if mysql has an empty root password
        mysql -e "select now()" >/dev/null
        if [ $? -eq 0 ]; then
            final_mysql_password='';
        fi


        echo "Installing database from SQL dump... $issabel_root_password"
        mysql -u root $final_mysql_password < /usr/share/issabelpbx/tmp/issabelpbx-database-dump-%{version}.%{release}.sql
        ret=$?
        if [ $ret -ne 0 ] ; then
               	exit $ret
        fi	

        echo "Grant access to asteriskuser to databases..."
        mysql -u root $final_mysql_password -e "GRANT ALL ON asterisk.* TO asteriskuser@localhost IDENTIFIED BY 'palosanto'"

        # Ruta a módulos es incorrecta en 64 bits. Se corrige a partir de ruta de Asterisk.
        if [ -f /etc/asterisk/asterisk.conf ]; then
        RUTAREAL=`grep astmoddir /etc/asterisk/asterisk.conf | sed 's|^.* \(/.\+\)$|\1|' -`
        sed --in-place "s|/usr/lib/asterisk/modules|$RUTAREAL|g" /etc/asterisk.issabel/asterisk.conf
        sed --in-place "s|/usr/lib/asterisk/modules|$RUTAREAL|g" /etc/amportal.conf
        fi

        # Si existe un archivo cbmysql.conf se reemplaza
        if [ -f /etc/asterisk/cbmysql.conf ] ; then
            echo "2 Saving cbmysql.conf ...";
            mv /etc/asterisk/cbmysql.conf /etc/asterisk/cbmysql.conf.bak_%{name}-%{version}-%{release}
        fi

        # Cambio carpeta de archivos de configuración de Asterisk
        if [ ! -d /etc/asterisk ]; then
            mkdir /etc/asterisk
            chown asterisk.asterisk /etc/asterisk
        fi
        mv -f /etc/asterisk.issabel/* /etc/asterisk/
        
        # Restaurar archivo cbmysql.conf previo
        if [ -f /etc/asterisk/cbmysql.conf.bak_%{name}-%{version}-%{release} ] ; then
            echo "2 Restoring cbmysql.conf ...";
            mv /etc/asterisk/cbmysql.conf.bak_%{name}-%{version}-%{release} /etc/asterisk/cbmysql.conf
        fi
    fi
        echo "2 ejecuto module admin para instalar timeconditions y customcontexts" >>/tmp/issabel_rpm.log

    /var/lib/asterisk/bin/module_admin install timeconditions
    /var/lib/asterisk/bin/module_admin install customcontexts
    echo "2 ejecuto module admin para instalar local" >>/tmp/issabel_rpm.log
    /var/lib/asterisk/bin/module_admin installlocal

# La base de datos esta apagada
else
    # La base de datos existe
    if [ -d "/var/lib/mysql/asterisk" ]; then
        # Abortar instalacion
        echo "3 base apagada aborto instalacion" >>/tmp/issabel_rpm.log

        echo "MySQL service down!!!. Please start this service before installing this RPM. Aborting..."
        exit 255
    # La base de datos NO existe
    else
        # Creo la base de datos, incluido el esquema de usuario/permiso
        echo "3 base existe , asumo instalacion iso, esperamos instalacion de base de datos hasta el primer boot" >>/tmp/issabel_rpm.log
        echo "Assumed ISO installation. Delayed database installation until first Issabel boot..."
        cp /usr/share/issabelpbx/tmp/issabelpbx-database-dump-%{version}.%{release}.sql /var/spool/issabel-mysqldbscripts/01-issabelpbx.sql

        # Ruta a módulos es incorrecta en 64 bits. Se corrige a partir de ruta de Asterisk.
        RUTAREAL=`grep astmoddir /etc/asterisk/asterisk.conf | sed 's|^.* \(/.\+\)$|\1|' -`
        sed --in-place "s|/usr/lib/asterisk/modules|$RUTAREAL|g" /etc/asterisk.issabel/asterisk.conf
        sed --in-place "s|/usr/lib/asterisk/modules|$RUTAREAL|g" /etc/amportal.conf

        # Cambio carpeta de archivos de configuración de Asterisk
        mv -f /etc/asterisk.issabel/* /etc/asterisk/

        if [ ! -f "/etc/asterisk/extensions_custom.conf" ]; then
            echo "cp /etc/asterisk/extensions_custom.conf.sample /etc/asterisk/extensions_custom.conf" >>/tmp/issabel_rpm.log
            cp /etc/asterisk/extensions_custom.conf.sample /etc/asterisk/extensions_custom.conf
        fi

        echo "3 instalo local" >>/tmp/issabel_rpm.log
        /var/lib/asterisk/bin/module_admin installlocal

        chown -R asterisk.asterisk /var/www/html/
    fi
fi

# Fixed bug, module recordins missed when is updated.
# Only update path /usr/share/issabelpbx/tmp/issabelpbx-2.11.0/
# exits, looking up about it.
if [ $1 -eq 2 ]; then #rpm update
    ##echo "RECORDINGS MODULE INSTALL(POST)"
    ##rm -rf /var/www/html/recordings/
    ##cp -ra /usr/share/issabelpbx/tmp/issabelpbx/amp_conf/htdocs/recordings/  /var/www/html/
    ##cp -ra /var/www/html/recordings/theme/images/* /var/www/html/admin/images/

    # Force regeneration of modules table after UPGRADE
    echo "4 trunco las tablas module y module_xml" >>/tmp/issabel_rpm.log

    issabel_root_password=`grep mysqlrootpwd= /etc/issabel.conf | sed 's/^mysqlrootpwd=//'`

    final_mysql_password="-p$issabel_root_password"

    # Try to check if mysql has an empty root password
    mysql -e "select now()" >/dev/null
    if [ $? -eq 0 ]; then
        final_mysql_password='';
    fi

    mysql -u root $final_mysql_password asterisk -e "TRUNCATE TABLE modules"
    mysql -u root $final_mysql_password asterisk -e "TRUNCATE TABLE module_xml"


    echo "4 instalo timecondition y customcontext" >>/tmp/issabel_rpm.log
    /var/lib/asterisk/bin/module_admin install timeconditions
    /var/lib/asterisk/bin/module_admin install customcontexts
    echo "4 instalo local" >>/tmp/issabel_rpm.log
    /var/lib/asterisk/bin/module_admin installlocal

    # Copies files that mighty not be present on older versions
    # Maybe all the /etc/asterisk.issabel directory needs to be copied...?
    cp /etc/asterisk.issabel/res_parking.conf /etc/asterisk/

fi

# Creo unos links simbolicos para algunos archivos de configuracion.
# Esto solo es necesario en una instalacion desde cero, donde no se ejecute el comando
# ./install_amp, pues este comando ya realiza esta tarea

# FIXME: Estas lineas deberían ser mas inteligentes y crear un ln para cada 
#        archivo encontrado en la carpeta /var/www/html/admin/modules/core/etc/

# Primero reviso si hay archivos de configuracion que sean archivos normales (no symlinks)
# Esto es porque por omision asterisk pone estos archivos y hay que borrarlos
# Ademas en versiones del este rpm menores a 2.4 se ponian archivos regulares en lugar de los
# links a los archivos dentro de issabelpbx, lo cual estaba mal
for etcdir in `find /var/www/html/admin/modules/ -name etc` ; do
# TODO: parece que issabelpbx está sobreescribiendo /etc/asterisk/cbmysql.conf con su propia versión, destruye contraseña de mysql
# TODO: hay que testear si /etc/asterisk/$BN es realmente un symlink, que no esté roto, y que realmente apunta a $astconf
#       podría bastar con borrar y volver a crear si es realmente un symlink ( verificar si funciona test con -h )
    for astconf in $etcdir/*conf ; do
        BN=`basename $astconf`
        if [ -f "/etc/asterisk/$BN" ] ; then
            if [ -h "/etc/asterisk/$BN" ] ; then
                echo "Deleting old symlink /etc/asterisk/$BN ..."
                rm -f /etc/asterisk/$BN
            else
                echo "Backing up old /etc/asterisk/$BN as /etc/asterisk/$BN.old_%{name}-%{version}-%{release}"
                mv /etc/asterisk/$BN /etc/asterisk/$BN.old_%{name}-%{version}-%{release}
            fi
        fi
        if [ ! -e "/etc/asterisk/$BN" ]; then
            ln -s $astconf /etc/asterisk/$BN
        fi        
    done
done

#if [ -f "/var/lib/asterisk/bin/fax-process.pl" ] ; then
#    echo "Backing up old /var/lib/asterisk/bin/fax-process.pl as /var/lib/asterisk/bin/fax-process.pl.old_%{name}-%{version}-%{release}"
#    mv /var/lib/asterisk/bin/fax-process.pl /var/lib/asterisk/bin/fax-process.pl.old_%{name}-%{version}-%{release}
#fi
#if [ ! -e "/var/lib/asterisk/bin/fax-process.pl" ]; then
#    ln -s /var/www/html/admin/modules/core/bin/fax-process.pl /var/lib/asterisk/bin/fax-process.pl
#fi


if [ ! -e "/var/www/html/modules/ivr/assets/images/" ]; then
    mkdir -p /var/www/html/modules/ivr/assets/
    ln -s /var/www/html/admin/modules/ivr/assets/images/ /var/www/html/modules/ivr/assets/images
fi
if [ ! -e "/var/www/html/images/notify_critical.png" ]; then
    ln -s /var/www/html/admin/images/notify_critical.png /var/www/html/images/notify_critical.png
fi
if [ ! -e "/var/www/html/assets/" ]; then
    ln -s /var/www/html/admin/assets/ /var/www/html/assets
    chown -R asterisk.asterisk /var/www/html/assets
fi

if [ -f "/var/lib/asterisk/bin/one_touch_record.php" ] ; then
    echo "Backing up old /var/lib/asterisk/bin/one_touch_record.php as /var/lib/asterisk/bin/one_touch_record.php.old_%{name}-%{version}-%{release}"
    mv /var/lib/asterisk/bin/one_touch_record.php /var/lib/asterisk/bin/one_touch_record.php.old_%{name}-%{version}-%{release}
fi
if [ ! -e "/var/lib/asterisk/bin/one_touch_record.php" ]; then
    ln -s /var/www/html/admin/modules/callrecording/bin/one_touch_record.php /var/lib/asterisk/bin/one_touch_record.php
    chown -R asterisk.asterisk /var/lib/asterisk/bin/one_touch_record.php
fi


# The following files must exist (even if empty) for asterisk 1.6.x to work correctly.
# This does not belong in %%install because these files are dynamically created.
touch /etc/asterisk/manager_additional.conf
touch /etc/asterisk/sip_general_custom.conf
touch /etc/asterisk/sip_nat.conf
touch /etc/asterisk/sip_registrations_custom.conf
touch /etc/asterisk/sip_registrations.conf
touch /etc/asterisk/sip_custom.conf
touch /etc/asterisk/sip_additional.conf
touch /etc/asterisk/sip_custom_post.conf
touch /etc/asterisk/extensions_override_issabelpbx.conf
touch /etc/asterisk/features_general_additional.conf
touch /etc/asterisk/sip_general_additional.conf
touch /etc/asterisk/queues_general_additional.conf
touch /etc/asterisk/dahdi-channels.conf
touch /etc/asterisk/meetme_additional.conf
touch /etc/asterisk/sip_general_additional.conf
touch /etc/asterisk/iax_general_additional.conf
touch /etc/asterisk/musiconhold_custom.conf
touch /etc/asterisk/extensions_additional.conf
touch /etc/asterisk/features_general_custom.conf
touch /etc/asterisk/queues_custom_general.conf
touch /etc/asterisk/chan_dahdi_additional.conf
touch /etc/asterisk/iax_registrations_custom.conf
touch /etc/asterisk/features_applicationmap_additional.conf
touch /etc/asterisk/queues_custom.conf
touch /etc/asterisk/iax_registrations.conf
touch /etc/asterisk/features_applicationmap_custom.conf
touch /etc/asterisk/queues_additional.conf
touch /etc/asterisk/iax_custom.conf
touch /etc/asterisk/features_featuremap_additional.conf
touch /etc/asterisk/queues_post_custom.conf
touch /etc/asterisk/iax_additional.conf
touch /etc/asterisk/features_featuremap_custom.conf
touch /etc/asterisk/iax_custom_post.conf
touch /etc/asterisk/sip_notify_additional.conf
touch /etc/asterisk/sip_notify_custom.conf
touch /etc/asterisk/cel_general_additional.conf
touch /etc/asterisk/cel_general_custom.conf
touch /etc/asterisk/cel_custom_post.conf
touch /etc/asterisk/cel_odbc_custom.conf
touch /etc/asterisk/udptl_custom.conf

touch /etc/asterisk/logger_general_custom.conf
touch /etc/asterisk/logger_logfiles_custom.conf

# Al momento de instalacion el archivo features_featuremap_custom.conf no cuenta con la linea
# de configuración que permite al feature code de "In-Call Asterisk Toggle Call Recording"
# funcionar correctamente
[ -s /etc/asterisk/features_featuremap_custom.conf ]
a=$(echo $?)
if [ "$a" -ne "0" ]; then #archivo vacio
	echo "automon=*1" >> /etc/asterisk/features_featuremap_custom.conf
# existe en el archivo pero es diferente al valor deseado
elif [ "$(cat /etc/asterisk/features_featuremap_custom.conf | grep -i "automon=")" != "automon=*1" ]; then
	sed -i -e "s/automon=\*[0,2,3,4,5,6,7,8,9]/automon=\*1/" /etc/asterisk/features_featuremap_custom.conf
# existe en el archivo
elif [ "$(cat /etc/asterisk/features_featuremap_custom.conf | grep -i "automon=")" == "automon=*1" ]; then #existe en el archivo
	echo "Nothing to be done..."
fi

chown -R asterisk.asterisk /etc/asterisk/*

# Algo mas de soporte para cuando se actualiza el issabelPBX desde su administracion
# Se crea estas carpetas para manejar un error de actualizacion
mkdir -p /var/www/html/_asterisk
##mkdir -p /var/www/html/recordings
mkdir -p /var/www/html/admin/modules/_cache
mkdir -p /var/lib/asterisk/mohmp3/none/
chown asterisk.asterisk /var/www/html/_asterisk 
##chown asterisk.asterisk /var/www/html/recordings
chown asterisk.asterisk /var/www/html/admin/modules/_cache
chown asterisk.asterisk /var/lib/asterisk/mohmp3/none/
chown asterisk.asterisk /etc/amportal.conf

# Fixed bug in FOP when we use DAHDI instead of Zap
#sed -ie "s/Zap/DAHDI/g" /var/lib/asterisk/bin/retrieve_op_conf_from_mysql.pl

# Fix once and for all the issue of recordings/MOH failing because
# of Access Denied errors.
if [ ! -e /var/lib/asterisk/sounds/custom/ ] ; then
    mkdir -p /var/lib/asterisk/sounds/custom/
    chown -R asterisk.asterisk /var/lib/asterisk/sounds/custom/
fi

# Copy any unaccounted files from moh to mohmp3
for i in /var/lib/asterisk/moh/* ; do
    if [ -e $i ] ; then
        BN=`basename "$i"`
        if [ ! -e "/var/lib/asterisk/mohmp3/$BN" ] ; then
            cp $i /var/lib/asterisk/mohmp3/
        fi
    fi
done

# Explicitly set MOHDIR=mohmp3 in amportal.conf
if ! grep -q -s '^MOHDIR' /etc/amportal.conf ; then
    echo 'No MOHDIR directive found in /etc/amportal.conf, setting to mohmp3 ...'
    echo -e "\n\nMOHDIR=mohmp3" >> /etc/amportal.conf
fi
if grep -q -s '^MOHDIR=moh$' /etc/amportal.conf ; then
    echo "Fixing MOHDIR to point to mohmp3 instead of moh in /etc/amportal.conf ..."
    sed -i "s/^MOHDIR=moh$/MOHDIR=mohmp3/" /etc/amportal.conf
fi

# Change moh to mohmp3 on all Asterisk configuration files touched by IssabelPBX
for i in /etc/asterisk/musiconhold*.conf ; do
    if ! grep -q -s '^directory=/var/lib/asterisk/moh$' $i ; then
        echo "Replacing instances of moh with mohmp3 in $i ..."
        sed -i "s|^directory=/var/lib/asterisk/moh\(/\)\?$|directory=/var/lib/asterisk/mohmp3/|" $i
    fi
done

%triggerin -- gettext
echo "Compling IssabelPBX translation files ..."
# Recompile gettext .po files
for A in `find /var/www/html/admin/modules -name \*.po`
do
POFILE=${A}
MOFILE=${POFILE%.po}.mo
PODIR=${A%$POFILE}
msgfmt $POFILE -o $MOFILE
done
systemctl restart httpd


%clean
rm -rf $RPM_BUILD_ROOT

# basic contains some reasonable sane basic tiles
%files
%defattr(-, asterisk, asterisk)
/etc/asterisk.issabel/*
/var/www/html/admin
/var/www/html/config.php
#/var/log/asterisk/*
#%config(noreplace) /var/www/html/admin/modules/fw_fop/op_buttons_additional.cfg
#%config(noreplace) /var/www/html/admin/modules/fw_fop/op_buttons_custom.cfg
/var/lib/asterisk/*
%config(noreplace) /var/log/asterisk/cdr-csv/Master.csv
%dir /var/log/asterisk/cdr-csv
%config(noreplace) /etc/amportal.conf
%defattr(-, root, root)
/etc/rc.d
/usr/sbin/amportal
/usr/share/issabelpbx/tmp/*
/etc/pbx/aver.flag

%changelog
