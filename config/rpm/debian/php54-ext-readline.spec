Name:		php54-ext-readline
Version:	5.4.16
Release:	1%{?dist}
Summary:	php54-ext-readline

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	php-5.4.16.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)
#BuildRequires:	libreadline-dev
#BuildRequires:	libedit-dev

%description



%prep
%setup -q -n php-5.4.16


%build
cd ext/readline
phpize
%configure
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
cd ext/readline
make install INSTALL_ROOT=%{buildroot}


%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/lib64/extensions/no-debug-non-zts-20100525/readline.so

%changelog

