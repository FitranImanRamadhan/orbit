#!/bin/bash

BASE="public/assets/vendor"

echo "Membuat folder vendor..."
mkdir -p $BASE

# Folder sesuai struktur
mkdir -p $BASE/bootstrap/css
mkdir -p $BASE/bootstrap/js
mkdir -p $BASE/bootstrap-icons
mkdir -p $BASE/sweetalert2
mkdir -p $BASE/boxicons/css
mkdir -p $BASE/boxicons/fonts
mkdir -p $BASE/datatables/css
mkdir -p $BASE/datatables/js
mkdir -p $BASE/fontawesome/css
mkdir -p $BASE/fontawesome/webfonts
mkdir -p $BASE/bootstrap-select
mkdir -p $BASE/jquery-ui/css
mkdir -p $BASE/jquery-ui/js
mkdir -p $BASE/jquery

echo "Copying Bootstrap..."
cp node_modules/bootstrap/dist/css/bootstrap.min.css $BASE/bootstrap/css/
cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js $BASE/bootstrap/js/

echo "Copying Bootstrap Icons..."
cp -r node_modules/bootstrap-icons/font/* $BASE/bootstrap-icons/

echo "Copying SweetAlert2..."
cp node_modules/sweetalert2/dist/sweetalert2.min.css $BASE/sweetalert2/
cp node_modules/sweetalert2/dist/sweetalert2.all.min.js $BASE/sweetalert2/

echo "Copying Boxicons..."
cp node_modules/boxicons/css/boxicons.min.css $BASE/boxicons/css/
cp -r node_modules/boxicons/fonts/* $BASE/boxicons/fonts/

echo "Copying DataTables..."
# CSS
cp node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css $BASE/datatables/css/
cp node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css $BASE/datatables/css/

# JS
cp node_modules/datatables.net/js/dataTables.min.js $BASE/datatables/js/
cp node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js $BASE/datatables/js/
cp node_modules/datatables.net-responsive/js/dataTables.responsive.min.js $BASE/datatables/js/
cp node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js $BASE/datatables/js/

echo "Copying Font Awesome..."
cp node_modules/@fortawesome/fontawesome-free/css/all.min.css $BASE/fontawesome/css/
cp -r node_modules/@fortawesome/fontawesome-free/webfonts/* $BASE/fontawesome/webfonts/

echo "Copying Bootstrap Select..."
cp node_modules/bootstrap-select/dist/css/bootstrap-select.min.css $BASE/bootstrap-select/
cp node_modules/bootstrap-select/dist/js/bootstrap-select.min.js $BASE/bootstrap-select/

echo "Copying jQuery UI..."
# CSS (fix, menggunakan file yang benar)
cp node_modules/jquery-ui/themes/base/jquery-ui.css $BASE/jquery-ui/css/

# Theme (jika ada theme.min.css gunakan yang itu)
if [ -f node_modules/jquery-ui/themes/base/theme.min.css ]; then
    cp node_modules/jquery-ui/themes/base/theme.min.css $BASE/jquery-ui/css/
else
    cp node_modules/jquery-ui/themes/base/theme.css $BASE/jquery-ui/css/
fi

# JS
cp node_modules/jquery-ui/dist/jquery-ui.min.js $BASE/jquery-ui/js/

echo "Copying jQuery..."
cp node_modules/jquery/dist/jquery.min.js $BASE/jquery/

echo "DONE ✓ Semua asset sudah dicopy ke public/assets/vendor/"
