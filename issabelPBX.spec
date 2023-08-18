Name:           issabelPBX
Version:        2.12.0
Release:        1%{?dist}
Summary:        issabelPBX

License:        GPL
URL:            www.issabel.org
Source0:        issabelPBX-2.12.0.tar.gz

BuildRequires:  bash php php-cli
Requires:       php php-mysqlnd php-mbstring asterisk mariadb perl-Digest-MD5 perl-URI perl-libwww-perl

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

%post

pear install DB >/dev/null 2>&1

/usr/src/issabelPBX/framework/install_amp --dbuser=root --installdb --scripted --language=en

# Compile .po files to .mo
for A in `find /var/www/html/admin -name \*.po`
do
POFILE=${A}
MOFILE=${POFILE%.po}.mo
PODIR=${A%$POFILE}
sudo -u asterisk msgfmt $POFILE -o $MOFILE
done

%files
/usr/src/issabelPBX/

%doc

%changelog
