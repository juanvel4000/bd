#!/bin/bash
echo $(git describe --tags) > VERSION

cat << EOF > bd/version.php
<?php 
\$version = '$(git describe --tags)';
EOF

