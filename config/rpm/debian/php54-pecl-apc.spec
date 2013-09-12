Name:		php54-pecl-apc
Version:	3.1.9
Release:	1%{?dist}
Summary:	php54-pecl-apc
# http://pecl.php.net/get/APC-3.1.9.tgz

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	APC-3.1.9.tgz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description

%prep
%setup -q -n APC-3.1.9


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
/usr/lib64/extensions/no-debug-non-zts-20100525/apc.so
/usr/include/php/ext/apc/apc_serializer.h

%changelog

