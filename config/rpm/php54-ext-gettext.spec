Name:		php54-ext-gettext
Version:	5.4.10
Release:	1%{?dist}
Summary:	php54-ext-gettext

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	php-5.4.10.tar.bz2
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q -n php-5.4.10


%build
cd ext/gettext
phpize
%configure
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
cd ext/gettext
make install INSTALL_ROOT=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/lib64/extensions/no-debug-non-zts-20100525/gettext.so

%changelog

