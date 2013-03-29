Name:		gdal
Version:	1.9.2
Release:	1%{?dist}
Summary:        gdal	

Group:		Hisoku
License:	No
URL:		http://hisoku.ronny.tw/
Source0:	gdal-1.9.2.tar.gz
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

%description


%prep
%setup -q

%build
./configure --with-libiconv-prefix=/usr/local
make %{?_smp_mflags}


%install
rm -rf %{buildroot}
make install DESTDIR=%{buildroot}


%clean
rm -rf %{buildroot}

%files
%defattr(-,root,root,-)
%doc
/usr/local/bin/gdal-config
/usr/local/bin/gdal_contour
/usr/local/bin/gdal_grid
/usr/local/bin/gdal_rasterize
/usr/local/bin/gdal_translate
/usr/local/bin/gdaladdo
/usr/local/bin/gdalbuildvrt
/usr/local/bin/gdaldem
/usr/local/bin/gdalenhance
/usr/local/bin/gdalinfo
/usr/local/bin/gdallocationinfo
/usr/local/bin/gdalmanage
/usr/local/bin/gdalsrsinfo
/usr/local/bin/gdaltindex
/usr/local/bin/gdaltransform
/usr/local/bin/gdalwarp
/usr/local/bin/nearblack
/usr/local/bin/ogr2ogr
/usr/local/bin/ogrinfo
/usr/local/bin/ogrtindex
/usr/local/bin/testepsg
/usr/local/include/cpl_atomic_ops.h
/usr/local/include/cpl_config.h
/usr/local/include/cpl_config_extras.h
/usr/local/include/cpl_conv.h
/usr/local/include/cpl_csv.h
/usr/local/include/cpl_error.h
/usr/local/include/cpl_hash_set.h
/usr/local/include/cpl_http.h
/usr/local/include/cpl_list.h
/usr/local/include/cpl_minixml.h
/usr/local/include/cpl_minizip_ioapi.h
/usr/local/include/cpl_minizip_unzip.h
/usr/local/include/cpl_minizip_zip.h
/usr/local/include/cpl_multiproc.h
/usr/local/include/cpl_odbc.h
/usr/local/include/cpl_port.h
/usr/local/include/cpl_quad_tree.h
/usr/local/include/cpl_string.h
/usr/local/include/cpl_time.h
/usr/local/include/cpl_vsi.h
/usr/local/include/cpl_vsi_virtual.h
/usr/local/include/cpl_win32ce_api.h
/usr/local/include/cpl_wince.h
/usr/local/include/cplkeywordparser.h
/usr/local/include/gdal.h
/usr/local/include/gdal_alg.h
/usr/local/include/gdal_alg_priv.h
/usr/local/include/gdal_csv.h
/usr/local/include/gdal_frmts.h
/usr/local/include/gdal_pam.h
/usr/local/include/gdal_priv.h
/usr/local/include/gdal_proxy.h
/usr/local/include/gdal_rat.h
/usr/local/include/gdal_version.h
/usr/local/include/gdal_vrt.h
/usr/local/include/gdalgrid.h
/usr/local/include/gdaljp2metadata.h
/usr/local/include/gdalwarper.h
/usr/local/include/gdalwarpkernel_opencl.h
/usr/local/include/gvgcpfit.h
/usr/local/include/memdataset.h
/usr/local/include/ogr_api.h
/usr/local/include/ogr_core.h
/usr/local/include/ogr_feature.h
/usr/local/include/ogr_featurestyle.h
/usr/local/include/ogr_geometry.h
/usr/local/include/ogr_p.h
/usr/local/include/ogr_spatialref.h
/usr/local/include/ogr_srs_api.h
/usr/local/include/ogrsf_frmts.h
/usr/local/include/rawdataset.h
/usr/local/include/thinplatespline.h
/usr/local/include/vrtdataset.h
/usr/local/lib/libgdal.a
/usr/local/lib/libgdal.la
/usr/local/lib/libgdal.so
/usr/local/lib/libgdal.so.1
/usr/local/lib/libgdal.so.1.16.2
/usr/local/share/gdal/GDALLogoBW.svg
/usr/local/share/gdal/GDALLogoColor.svg
/usr/local/share/gdal/GDALLogoGS.svg
/usr/local/share/gdal/LICENSE.TXT
/usr/local/share/gdal/compdcs.csv
/usr/local/share/gdal/coordinate_axis.csv
/usr/local/share/gdal/cubewerx_extra.wkt
/usr/local/share/gdal/datum_shift.csv
/usr/local/share/gdal/ecw_cs.wkt
/usr/local/share/gdal/ellipsoid.csv
/usr/local/share/gdal/epsg.wkt
/usr/local/share/gdal/esri_StatePlane_extra.wkt
/usr/local/share/gdal/esri_Wisconsin_extra.wkt
/usr/local/share/gdal/esri_extra.wkt
/usr/local/share/gdal/gcs.csv
/usr/local/share/gdal/gcs.override.csv
/usr/local/share/gdal/gdal_datum.csv
/usr/local/share/gdal/gdalicon.png
/usr/local/share/gdal/geoccs.csv
/usr/local/share/gdal/gt_datum.csv
/usr/local/share/gdal/gt_ellips.csv
/usr/local/share/gdal/header.dxf
/usr/local/share/gdal/nitf_spec.xml
/usr/local/share/gdal/nitf_spec.xsd
/usr/local/share/gdal/pci_datum.txt
/usr/local/share/gdal/pci_ellips.txt
/usr/local/share/gdal/pcs.csv
/usr/local/share/gdal/pcs.override.csv
/usr/local/share/gdal/prime_meridian.csv
/usr/local/share/gdal/projop_wparm.csv
/usr/local/share/gdal/s57agencies.csv
/usr/local/share/gdal/s57attributes.csv
/usr/local/share/gdal/s57attributes_aml.csv
/usr/local/share/gdal/s57attributes_iw.csv
/usr/local/share/gdal/s57expectedinput.csv
/usr/local/share/gdal/s57objectclasses.csv
/usr/local/share/gdal/s57objectclasses_aml.csv
/usr/local/share/gdal/s57objectclasses_iw.csv
/usr/local/share/gdal/seed_2d.dgn
/usr/local/share/gdal/seed_3d.dgn
/usr/local/share/gdal/stateplane.csv
/usr/local/share/gdal/trailer.dxf
/usr/local/share/gdal/unit_of_measure.csv
/usr/local/share/gdal/vertcs.csv
/usr/local/share/gdal/vertcs.override.csv

%changelog

