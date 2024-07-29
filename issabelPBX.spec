Name:           issabelPBX
Version:        2.12.0
Release:        4
Summary:        issabelPBX

License:        GPL
URL:            www.issabel.org
Source0:        issabelPBX-2.12.0.tar.gz
BuildArch:      noarch

BuildRequires:  bash php php-cli
Requires(pre):  asterisk
Requires:       php php-mysqlnd php-mbstring mariadb perl-Digest-MD5 perl-URI perl-libwww-perl sox mpg123 perl-LWP-Protocol-https

%{?el8:Requires: php-pear php-pear-DB}

%undefine __brp_mangle_shebangs
%define debug_package %{nil}

%description
issabelPBX

%prep
%setup -q

%build

%install
mkdir -p %{buildroot}/usr/src/issabelPBX
cp -a * %{buildroot}/usr/src/issabelPBX

mkdir -p  %{buildroot}/etc/logrotate.d/
cp        %{buildroot}/usr/src/issabelPBX/build/5.0/files/issabelpbx.logrotate  %{buildroot}/etc/logrotate.d/
chmod 644 %{buildroot}/etc/logrotate.d/*

%post

pear install DB >/dev/null 2>&1 || :

PODIR=/usr/src/issabelPBX

killall -q -0 asterisk
if [ $? -eq 0 ]; then
echo "asterisk is up and running"
killall -q -0 mysqld
if [ $? -eq 0 ]; then
echo "mariadb is up and running"
echo "perform installation"
if [ $? -eq 1 ]; then
/usr/src/issabelPBX/framework/install_amp --dbuser=root --installdb --scripted --language=en || :
else
MYSQL_ROOTPWD=`grep mysqlrootpwd= /etc/issabel.conf | sed 's/^mysqlrootpwd=//'`
/usr/src/issabelPBX/framework/install_amp --dbuser=root --dbpass=$MYSQL_ROOTPWD --scripted --language=en || :
fi
PODIR=/var/www/html/admin
else
echo "mariadb is not running, installation process has been skipped"
touch /installamp
fi
else
echo "asterisk is not running, installation process has been skipped"
touch /installamp
fi

# Compile .po files to .mo
for A in `find $PODIR -name \*.po`
do
POFILE=${A}
MOFILE=${POFILE%.po}.mo
PODIR=${A%$POFILE}
sudo -u asterisk msgfmt $POFILE -o $MOFILE || :
done

chmod 644 /etc/logrotate.d/issabelpbx.logrotate

%files
/usr/src/issabelPBX/
%config(noreplace) /etc/logrotate.d/issabelpbx.logrotate

%doc

%changelog
