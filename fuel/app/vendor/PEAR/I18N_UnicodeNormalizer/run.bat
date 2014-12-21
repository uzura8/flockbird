call pear uninstall --ignore-errors I18N_UnicodeNormalizer
call pear package-validate package2.xml
call pear package package2.xml
call pear install I18N_UnicodeNormalizer-1.0.0.tgz