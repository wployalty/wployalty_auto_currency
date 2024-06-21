echo "WPLoyalty Auto Currency Compress pack"
current_dir="$PWD/"
pack_folder="wp-loyalty-auto-currency"
plugin_pack_folder="wployalty_auto_currency"
folder_sperate="/"
pack_compress_folder=$current_dir$pack_folder
composer_run() {
  cd "$plugin_pack_folder"
  composer install --no-dev -q
  composer update --no-dev -q
  cd ..
  echo "Compress Done"
}

copy_folder() {
  cd $current_dir
  from_folder="wployalty_auto_currency"
  from_folder_dir=$current_dir$from_folder
  move_dir=("App" "Assets" "i18n" "vendor" "readme.txt" "wp-loyalty-auto-currency.php")
  if [ -d "$pack_compress_folder" ]; then
    rm -r "$pack_folder"
    mkdir "$pack_folder"
    # shellcheck disable=SC2068
    for dir in ${move_dir[@]}; do
      cp -r "$from_folder_dir/$dir" "$pack_compress_folder/$dir"
    done
  else
    mkdir "$pack_folder"
    # shellcheck disable=SC2068
    for dir in ${move_dir[@]}; do
      cp -r "$from_folder_dir/$dir" "$pack_compress_folder/$dir"
    done
  fi
  echo "Copy Done"
}
zip_folder() {
  rm "$pack_folder".zip
  zip -r "$pack_folder".zip $pack_folder
  zip -d "$pack_folder".zip __MACOSX/\*
  zip -d "$pack_folder".zip \*/.DS_Store
}
echo "Composer Run:"
composer_run
echo "Copy Folder:"
copy_folder
echo "Zip Folder:"
zip_folder
echo "End"
