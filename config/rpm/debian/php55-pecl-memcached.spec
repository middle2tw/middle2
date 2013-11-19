Name:		php55-pecl-memcached
Version:	2.1.0
Release:	1%{?dist}
Summary:	php55-pecl-memcached

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
# http://pecl.php.net/get/memcached-2.1.0.tgz
Source0:	memcached-2.1.0.tgz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description

%prep
%setup -q -n memcached-2.1.0


%build
phpize
%configure
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
make install INSTALL_ROOT=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/lib64/extensions/no-debug-non-zts-20121212/memcached.so

%changelog

