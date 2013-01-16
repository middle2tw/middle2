Name:		php54-ext-curl
Version:	5.4.10
Release:	1%{?dist}
Summary:	php54-ext-curl

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	php-5.4.10.tar.bz2
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description

BuildRequires: libcurl-devel


%prep
%setup -q -n php-5.4.10


%build
cd ext/curl
phpize
%configure
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
cd ext/curl
make install INSTALL_ROOT=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/lib64/extensions/no-debug-non-zts-20100525/curl.so

%changelog

