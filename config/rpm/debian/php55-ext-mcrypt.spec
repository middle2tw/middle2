Name:		php55-ext-mcrypt
Version:	5.5.6
Release:	1%{?dist}
Summary:	php55-ext-mcrypt

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	php-5.5.6.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)
#BuildRequires:	libmcrypt-devel

%description



%prep
%setup -q -n php-5.5.6


%build
cd ext/mcrypt
phpize
%configure
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
cd ext/mcrypt
make install INSTALL_ROOT=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/lib64/extensions/no-debug-non-zts-20121212/mcrypt.so

%changelog

