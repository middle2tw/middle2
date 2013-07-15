Name:	        setuptools
Version:	0.9.1
Release:	1%{?dist}
Summary:	setuptools for Python 2.7

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
# https://pypi.python.org/packages/source/s/setuptools/setuptools-0.9.1.tar.gz
Source0:	setuptools-0.9.1.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)
%description


%prep
%setup -q

%build


%install
python setup.py install --root %{buildroot}

%clean
rm -rf %{buildroot}


%files
%defattr(-,root,root,-)
%doc
/usr/bin/easy_install
/usr/bin/easy_install-2.7
/usr/lib/python2.7/site-packages/_markerlib/__init__.py
/usr/lib/python2.7/site-packages/_markerlib/__init__.pyc
/usr/lib/python2.7/site-packages/_markerlib/markers.py
/usr/lib/python2.7/site-packages/_markerlib/markers.pyc
/usr/lib/python2.7/site-packages/easy_install.py
/usr/lib/python2.7/site-packages/easy_install.pyc
/usr/lib/python2.7/site-packages/pkg_resources.py
/usr/lib/python2.7/site-packages/pkg_resources.pyc
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/EGG-INFO/PKG-INFO
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/PKG-INFO
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/SOURCES.txt
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/dependency_links.txt
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/dependency_links.txt.orig
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/entry_points.txt
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/entry_points.txt.orig
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/requires.txt
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/requires.txt.orig
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/top_level.txt
/usr/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/zip-safe
/usr/lib/python2.7/site-packages/setuptools/__init__.py
/usr/lib/python2.7/site-packages/setuptools/__init__.pyc
/usr/lib/python2.7/site-packages/setuptools/_backport/__init__.py
/usr/lib/python2.7/site-packages/setuptools/_backport/__init__.pyc
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/__init__.py
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/__init__.pyc
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha.py
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha.pyc
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha256.py
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha256.pyc
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha512.py
/usr/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha512.pyc
/usr/lib/python2.7/site-packages/setuptools/archive_util.py
/usr/lib/python2.7/site-packages/setuptools/archive_util.pyc
/usr/lib/python2.7/site-packages/setuptools/command/__init__.py
/usr/lib/python2.7/site-packages/setuptools/command/__init__.pyc
/usr/lib/python2.7/site-packages/setuptools/command/alias.py
/usr/lib/python2.7/site-packages/setuptools/command/alias.pyc
/usr/lib/python2.7/site-packages/setuptools/command/bdist_egg.py
/usr/lib/python2.7/site-packages/setuptools/command/bdist_egg.pyc
/usr/lib/python2.7/site-packages/setuptools/command/bdist_rpm.py
/usr/lib/python2.7/site-packages/setuptools/command/bdist_rpm.pyc
/usr/lib/python2.7/site-packages/setuptools/command/bdist_wininst.py
/usr/lib/python2.7/site-packages/setuptools/command/bdist_wininst.pyc
/usr/lib/python2.7/site-packages/setuptools/command/build_ext.py
/usr/lib/python2.7/site-packages/setuptools/command/build_ext.pyc
/usr/lib/python2.7/site-packages/setuptools/command/build_py.py
/usr/lib/python2.7/site-packages/setuptools/command/build_py.pyc
/usr/lib/python2.7/site-packages/setuptools/command/develop.py
/usr/lib/python2.7/site-packages/setuptools/command/develop.pyc
/usr/lib/python2.7/site-packages/setuptools/command/easy_install.py
/usr/lib/python2.7/site-packages/setuptools/command/easy_install.pyc
/usr/lib/python2.7/site-packages/setuptools/command/egg_info.py
/usr/lib/python2.7/site-packages/setuptools/command/egg_info.pyc
/usr/lib/python2.7/site-packages/setuptools/command/install.py
/usr/lib/python2.7/site-packages/setuptools/command/install.pyc
/usr/lib/python2.7/site-packages/setuptools/command/install_egg_info.py
/usr/lib/python2.7/site-packages/setuptools/command/install_egg_info.pyc
/usr/lib/python2.7/site-packages/setuptools/command/install_lib.py
/usr/lib/python2.7/site-packages/setuptools/command/install_lib.pyc
/usr/lib/python2.7/site-packages/setuptools/command/install_scripts.py
/usr/lib/python2.7/site-packages/setuptools/command/install_scripts.pyc
/usr/lib/python2.7/site-packages/setuptools/command/register.py
/usr/lib/python2.7/site-packages/setuptools/command/register.pyc
/usr/lib/python2.7/site-packages/setuptools/command/rotate.py
/usr/lib/python2.7/site-packages/setuptools/command/rotate.pyc
/usr/lib/python2.7/site-packages/setuptools/command/saveopts.py
/usr/lib/python2.7/site-packages/setuptools/command/saveopts.pyc
/usr/lib/python2.7/site-packages/setuptools/command/sdist.py
/usr/lib/python2.7/site-packages/setuptools/command/sdist.pyc
/usr/lib/python2.7/site-packages/setuptools/command/setopt.py
/usr/lib/python2.7/site-packages/setuptools/command/setopt.pyc
/usr/lib/python2.7/site-packages/setuptools/command/test.py
/usr/lib/python2.7/site-packages/setuptools/command/test.pyc
/usr/lib/python2.7/site-packages/setuptools/command/upload.py
/usr/lib/python2.7/site-packages/setuptools/command/upload.pyc
/usr/lib/python2.7/site-packages/setuptools/command/upload_docs.py
/usr/lib/python2.7/site-packages/setuptools/command/upload_docs.pyc
/usr/lib/python2.7/site-packages/setuptools/compat.py
/usr/lib/python2.7/site-packages/setuptools/compat.pyc
/usr/lib/python2.7/site-packages/setuptools/depends.py
/usr/lib/python2.7/site-packages/setuptools/depends.pyc
/usr/lib/python2.7/site-packages/setuptools/dist.py
/usr/lib/python2.7/site-packages/setuptools/dist.pyc
/usr/lib/python2.7/site-packages/setuptools/extension.py
/usr/lib/python2.7/site-packages/setuptools/extension.pyc
/usr/lib/python2.7/site-packages/setuptools/package_index.py
/usr/lib/python2.7/site-packages/setuptools/package_index.pyc
/usr/lib/python2.7/site-packages/setuptools/py24compat.py
/usr/lib/python2.7/site-packages/setuptools/py24compat.pyc
/usr/lib/python2.7/site-packages/setuptools/py27compat.py
/usr/lib/python2.7/site-packages/setuptools/py27compat.pyc
/usr/lib/python2.7/site-packages/setuptools/sandbox.py
/usr/lib/python2.7/site-packages/setuptools/sandbox.pyc
"/usr/lib/python2.7/site-packages/setuptools/script template (dev).py"
"/usr/lib/python2.7/site-packages/setuptools/script template (dev).pyc"
"/usr/lib/python2.7/site-packages/setuptools/script template.py"
"/usr/lib/python2.7/site-packages/setuptools/script template.pyc"
/usr/lib/python2.7/site-packages/setuptools/site-patch.py
/usr/lib/python2.7/site-packages/setuptools/site-patch.pyc
/usr/lib/python2.7/site-packages/setuptools/ssl_support.py
/usr/lib/python2.7/site-packages/setuptools/ssl_support.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/__init__.py
/usr/lib/python2.7/site-packages/setuptools/tests/__init__.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/doctest.py
/usr/lib/python2.7/site-packages/setuptools/tests/doctest.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/py26compat.py
/usr/lib/python2.7/site-packages/setuptools/tests/py26compat.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/server.py
/usr/lib/python2.7/site-packages/setuptools/tests/server.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_bdist_egg.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_bdist_egg.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_build_ext.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_build_ext.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_develop.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_develop.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_dist_info.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_dist_info.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_easy_install.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_easy_install.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_egg_info.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_egg_info.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_markerlib.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_markerlib.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_packageindex.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_packageindex.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_resources.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_resources.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_sandbox.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_sandbox.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_sdist.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_sdist.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_test.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_test.pyc
/usr/lib/python2.7/site-packages/setuptools/tests/test_upload_docs.py
/usr/lib/python2.7/site-packages/setuptools/tests/test_upload_docs.pyc

%changelog
