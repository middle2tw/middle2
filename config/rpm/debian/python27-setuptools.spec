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
/usr/local/bin/easy_install
/usr/local/bin/easy_install-2.7
/usr/local/lib/python2.7/dist-packages/_markerlib/__init__.py
/usr/local/lib/python2.7/dist-packages/_markerlib/__init__.pyc
/usr/local/lib/python2.7/dist-packages/_markerlib/markers.py
/usr/local/lib/python2.7/dist-packages/_markerlib/markers.pyc
/usr/local/lib/python2.7/dist-packages/easy_install.py
/usr/local/lib/python2.7/dist-packages/easy_install.pyc
/usr/local/lib/python2.7/dist-packages/pkg_resources.py
/usr/local/lib/python2.7/dist-packages/pkg_resources.pyc
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/EGG-INFO/PKG-INFO
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/PKG-INFO
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/SOURCES.txt
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/dependency_links.txt
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/dependency_links.txt.orig
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/entry_points.txt
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/entry_points.txt.orig
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/requires.txt
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/requires.txt.orig
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/top_level.txt
/usr/local/lib/python2.7/dist-packages/setuptools-0.9.1-py2.7.egg-info/zip-safe
/usr/local/lib/python2.7/dist-packages/setuptools/__init__.py
/usr/local/lib/python2.7/dist-packages/setuptools/__init__.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/__init__.py
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/__init__.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/__init__.py
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/__init__.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/_sha.py
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/_sha.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/_sha256.py
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/_sha256.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/_sha512.py
/usr/local/lib/python2.7/dist-packages/setuptools/_backport/hashlib/_sha512.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/archive_util.py
/usr/local/lib/python2.7/dist-packages/setuptools/archive_util.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/__init__.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/__init__.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/alias.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/alias.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/bdist_egg.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/bdist_egg.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/bdist_rpm.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/bdist_rpm.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/bdist_wininst.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/bdist_wininst.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/build_ext.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/build_ext.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/build_py.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/build_py.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/develop.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/develop.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/easy_install.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/easy_install.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/egg_info.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/egg_info.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/install.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/install.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/install_egg_info.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/install_egg_info.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/install_lib.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/install_lib.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/install_scripts.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/install_scripts.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/register.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/register.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/rotate.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/rotate.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/saveopts.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/saveopts.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/sdist.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/sdist.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/setopt.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/setopt.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/test.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/test.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/upload.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/upload.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/command/upload_docs.py
/usr/local/lib/python2.7/dist-packages/setuptools/command/upload_docs.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/compat.py
/usr/local/lib/python2.7/dist-packages/setuptools/compat.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/depends.py
/usr/local/lib/python2.7/dist-packages/setuptools/depends.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/dist.py
/usr/local/lib/python2.7/dist-packages/setuptools/dist.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/extension.py
/usr/local/lib/python2.7/dist-packages/setuptools/extension.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/package_index.py
/usr/local/lib/python2.7/dist-packages/setuptools/package_index.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/py24compat.py
/usr/local/lib/python2.7/dist-packages/setuptools/py24compat.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/py27compat.py
/usr/local/lib/python2.7/dist-packages/setuptools/py27compat.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/sandbox.py
/usr/local/lib/python2.7/dist-packages/setuptools/sandbox.pyc
"/usr/local/lib/python2.7/dist-packages/setuptools/script template (dev).py"
"/usr/local/lib/python2.7/dist-packages/setuptools/script template (dev).pyc"
"/usr/local/lib/python2.7/dist-packages/setuptools/script template.py"
"/usr/local/lib/python2.7/dist-packages/setuptools/script template.pyc"
/usr/local/lib/python2.7/dist-packages/setuptools/site-patch.py
/usr/local/lib/python2.7/dist-packages/setuptools/site-patch.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/ssl_support.py
/usr/local/lib/python2.7/dist-packages/setuptools/ssl_support.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/__init__.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/__init__.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/doctest.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/doctest.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/py26compat.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/py26compat.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/server.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/server.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_bdist_egg.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_bdist_egg.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_build_ext.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_build_ext.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_develop.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_develop.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_dist_info.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_dist_info.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_easy_install.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_easy_install.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_egg_info.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_egg_info.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_markerlib.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_markerlib.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_packageindex.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_packageindex.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_resources.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_resources.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_sandbox.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_sandbox.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_sdist.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_sdist.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_test.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_test.pyc
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_upload_docs.py
/usr/local/lib/python2.7/dist-packages/setuptools/tests/test_upload_docs.pyc
/usr/local/lib/python2.7/site-packages/_markerlib/__init__.py
/usr/local/lib/python2.7/site-packages/_markerlib/__init__.pyc
/usr/local/lib/python2.7/site-packages/_markerlib/markers.py
/usr/local/lib/python2.7/site-packages/_markerlib/markers.pyc
/usr/local/lib/python2.7/site-packages/easy_install.py
/usr/local/lib/python2.7/site-packages/easy_install.pyc
/usr/local/lib/python2.7/site-packages/pkg_resources.py
/usr/local/lib/python2.7/site-packages/pkg_resources.pyc
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/EGG-INFO/PKG-INFO
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/PKG-INFO
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/SOURCES.txt
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/dependency_links.txt
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/dependency_links.txt.orig
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/entry_points.txt
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/entry_points.txt.orig
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/requires.txt
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/requires.txt.orig
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/top_level.txt
/usr/local/lib/python2.7/site-packages/setuptools-0.9.1-py2.7.egg-info/zip-safe
/usr/local/lib/python2.7/site-packages/setuptools/__init__.py
/usr/local/lib/python2.7/site-packages/setuptools/__init__.pyc
/usr/local/lib/python2.7/site-packages/setuptools/_backport/__init__.py
/usr/local/lib/python2.7/site-packages/setuptools/_backport/__init__.pyc
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/__init__.py
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/__init__.pyc
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha.py
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha.pyc
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha256.py
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha256.pyc
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha512.py
/usr/local/lib/python2.7/site-packages/setuptools/_backport/hashlib/_sha512.pyc
/usr/local/lib/python2.7/site-packages/setuptools/archive_util.py
/usr/local/lib/python2.7/site-packages/setuptools/archive_util.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/__init__.py
/usr/local/lib/python2.7/site-packages/setuptools/command/__init__.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/alias.py
/usr/local/lib/python2.7/site-packages/setuptools/command/alias.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/bdist_egg.py
/usr/local/lib/python2.7/site-packages/setuptools/command/bdist_egg.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/bdist_rpm.py
/usr/local/lib/python2.7/site-packages/setuptools/command/bdist_rpm.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/bdist_wininst.py
/usr/local/lib/python2.7/site-packages/setuptools/command/bdist_wininst.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/build_ext.py
/usr/local/lib/python2.7/site-packages/setuptools/command/build_ext.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/build_py.py
/usr/local/lib/python2.7/site-packages/setuptools/command/build_py.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/develop.py
/usr/local/lib/python2.7/site-packages/setuptools/command/develop.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/easy_install.py
/usr/local/lib/python2.7/site-packages/setuptools/command/easy_install.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/egg_info.py
/usr/local/lib/python2.7/site-packages/setuptools/command/egg_info.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/install.py
/usr/local/lib/python2.7/site-packages/setuptools/command/install.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/install_egg_info.py
/usr/local/lib/python2.7/site-packages/setuptools/command/install_egg_info.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/install_lib.py
/usr/local/lib/python2.7/site-packages/setuptools/command/install_lib.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/install_scripts.py
/usr/local/lib/python2.7/site-packages/setuptools/command/install_scripts.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/register.py
/usr/local/lib/python2.7/site-packages/setuptools/command/register.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/rotate.py
/usr/local/lib/python2.7/site-packages/setuptools/command/rotate.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/saveopts.py
/usr/local/lib/python2.7/site-packages/setuptools/command/saveopts.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/sdist.py
/usr/local/lib/python2.7/site-packages/setuptools/command/sdist.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/setopt.py
/usr/local/lib/python2.7/site-packages/setuptools/command/setopt.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/test.py
/usr/local/lib/python2.7/site-packages/setuptools/command/test.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/upload.py
/usr/local/lib/python2.7/site-packages/setuptools/command/upload.pyc
/usr/local/lib/python2.7/site-packages/setuptools/command/upload_docs.py
/usr/local/lib/python2.7/site-packages/setuptools/command/upload_docs.pyc
/usr/local/lib/python2.7/site-packages/setuptools/compat.py
/usr/local/lib/python2.7/site-packages/setuptools/compat.pyc
/usr/local/lib/python2.7/site-packages/setuptools/depends.py
/usr/local/lib/python2.7/site-packages/setuptools/depends.pyc
/usr/local/lib/python2.7/site-packages/setuptools/dist.py
/usr/local/lib/python2.7/site-packages/setuptools/dist.pyc
/usr/local/lib/python2.7/site-packages/setuptools/extension.py
/usr/local/lib/python2.7/site-packages/setuptools/extension.pyc
/usr/local/lib/python2.7/site-packages/setuptools/package_index.py
/usr/local/lib/python2.7/site-packages/setuptools/package_index.pyc
/usr/local/lib/python2.7/site-packages/setuptools/py24compat.py
/usr/local/lib/python2.7/site-packages/setuptools/py24compat.pyc
/usr/local/lib/python2.7/site-packages/setuptools/py27compat.py
/usr/local/lib/python2.7/site-packages/setuptools/py27compat.pyc
/usr/local/lib/python2.7/site-packages/setuptools/sandbox.py
/usr/local/lib/python2.7/site-packages/setuptools/sandbox.pyc
"/usr/local/lib/python2.7/site-packages/setuptools/script template (dev).py"
"/usr/local/lib/python2.7/site-packages/setuptools/script template (dev).pyc"
"/usr/local/lib/python2.7/site-packages/setuptools/script template.py"
"/usr/local/lib/python2.7/site-packages/setuptools/script template.pyc"
/usr/local/lib/python2.7/site-packages/setuptools/site-patch.py
/usr/local/lib/python2.7/site-packages/setuptools/site-patch.pyc
/usr/local/lib/python2.7/site-packages/setuptools/ssl_support.py
/usr/local/lib/python2.7/site-packages/setuptools/ssl_support.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/__init__.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/__init__.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/doctest.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/doctest.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/py26compat.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/py26compat.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/server.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/server.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_bdist_egg.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_bdist_egg.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_build_ext.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_build_ext.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_develop.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_develop.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_dist_info.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_dist_info.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_easy_install.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_easy_install.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_egg_info.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_egg_info.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_markerlib.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_markerlib.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_packageindex.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_packageindex.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_resources.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_resources.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_sandbox.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_sandbox.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_sdist.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_sdist.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_test.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_test.pyc
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_upload_docs.py
/usr/local/lib/python2.7/site-packages/setuptools/tests/test_upload_docs.pyc

%changelog
