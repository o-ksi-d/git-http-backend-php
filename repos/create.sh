
AAP=$1
git init --bare r.php/$AAP

mv r.php/$AAP/hooks/post-update.sample r.php/$AAP/hooks/post-update
chmod a+x r.php/$AAP/hooks/post-update



htpasswd -b ../html/repos/.htpasswd testuser 1234

echo ~/.netrc 

echo git config --global http.postBuffer 5000000000

