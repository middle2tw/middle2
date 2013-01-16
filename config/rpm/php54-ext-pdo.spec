Name:		php54-ext-pdo
Version:	5.4.10
Release:	1%{?dist}
Summary:	php54-ext-pdo

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	php-5.4.10.tar.bz2
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n php-5.4.10


%build
cd ext/pdo
phpize
%configure
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
cd ext/pdo
make install INSTALL_ROOT=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/include/php/ext/pdo/php_pdo.h
/usr/include/php/ext/pdo/php_pdo_driver.h
/usr/lib64/extensions/no-debug-non-zts-20100525/pdo.so



%changelog

