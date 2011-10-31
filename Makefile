VERSION=""

define package_1
<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="1.8.0" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
    http://pear.php.net/dtd/tasks-1.0.xsd
    http://pear.php.net/dtd/package-2.0
    http://pear.php.net/dtd/package-2.0.xsd">
  <name>doxphp</name>
  <channel>pear.avalanche123.com</channel>
  <summary>
    Dox for PHP, inspired by Dox for JavaScript.
  </summary>
  <description>
    Dox for PHP is a documentation engine for PHP inspired by the Dox for JavaScript.
  </description>
  <lead>
    <name>Bulat Shakirzyanov</name>
    <user>avalanche123</user>
    <email>mallluhuct at gmail.com</email>
    <active>yes</active>
  </lead>
  <date>$(shell date +%Y-%m-%d)</date>
  <time>$(shell date +%H:%M:%S)</time>
  <version>
    <release>$(VERSION)</release>
    <api>$(VERSION)</api>
  </version>
  <stability>
    <release>beta</release>
    <api>beta</api>
  </stability>
  <license uri="http://www.opensource.org/licenses/mit-license.php">MIT</license>
  <notes>Early release.</notes>
  <contents>
    <dir name="/">
      <dir name="bin">
        <file name="doxphp" role="script" baseinstalldir="/" md5sum="$(shell md5 < bin/doxphp)">
          <tasks:replace type="pear-config" from="/usr/bin/env php" to="php_bin"/>
          <tasks:replace type="pear-config" from="@php_bin@" to="php_bin"/>
          <tasks:replace type="pear-config" from="@bin_dir@" to="bin_dir"/>
          <tasks:replace type="pear-config" from="@pear_directory@" to="php_dir"/>
        </file>
        <file name="doxphp2sphinx" role="script" baseinstalldir="/" md5sum="$(shell md5 < bin/doxphp2sphinx)">
          <tasks:replace type="pear-config" from="/usr/bin/env php" to="php_bin"/>
          <tasks:replace type="pear-config" from="@php_bin@" to="php_bin"/>
          <tasks:replace type="pear-config" from="@bin_dir@" to="bin_dir"/>
          <tasks:replace type="pear-config" from="@pear_directory@" to="php_dir"/>
        </file>
        <file name="doxphp2docco" role="script" baseinstalldir="/" md5sum="$(shell md5 < bin/doxphp2docco)">
          <tasks:replace type="pear-config" from="/usr/bin/env php" to="php_bin"/>
          <tasks:replace type="pear-config" from="@php_bin@" to="php_bin"/>
          <tasks:replace type="pear-config" from="@bin_dir@" to="bin_dir"/>
          <tasks:replace type="pear-config" from="@pear_directory@" to="php_dir"/>
        </file>
      </dir>
      <dir name="lib">
endef

define package_2
      </dir>
    </dir>
  </contents>
  <dependencies>
    <required>
      <php>
        <min>5.3.2</min>
      </php>
      <pearinstaller>
        <min>1.4.0</min>
      </pearinstaller>
    </required>
  </dependencies>
  <phprelease>
    <filelist>
      <install as="doxphp" name="bin/doxphp"/>
      <install as="doxphp2sphinx" name="bin/doxphp2sphinx"/>
      <install as="doxphp2docco" name="bin/doxphp2docco"/>
endef

define package_3
    </filelist>
  </phprelease>
</package>
endef

define composer
{
  "name": "doxphp/doxphp",
  "description": "Dox for PHP, inspired by Dox for JavaScript.",
  "keywords": ["documentation","phpdoc", "api docs"],
  "homepage": "http://github.com/avalanche123/doxphp/",
  "version": "$(VERSION)-dev",
  "license": "MIT",
  "authors": [
    {
      "name": "Bulat Shakirzyanov",
      "email": "mallluhuct@gmail.com",
      "homepage": "http://avalanche123.com"
    }
  ],
  "require": {
    "php": ">=5.3.2"
  }
}
endef

export package_1 package_2 package_3 composer

.PHONY: package release clean

clean:
	git clean -df

package:
	echo "$$package_1" > package.xml
	git ls-files lib | while read line; do echo "        <file md5sum=\"$$(md5 < $$line)\" name=\"$${line/#lib\/}\" role=\"php\"><tasks:replace type=\"pear-config\" from=\"@pear_directory@\" to=\"php_dir\"/></file>" >> package.xml; done;
	echo "$$package_2" >> package.xml
	git ls-files lib | while read line; do echo "      <install as=\"$${line/#lib\/}\" name=\"$$line\"/>" >> package.xml; done;
	echo "$$package_3" >> package.xml
	pear package
	rm -f package.xml

release:
	echo "$$composer" > composer.json
	git add composer.json; git commit -m "updated composer.json for $(VERSION) release"
	@echo "composer.json updated"
	make package
	@echo "a new package docco-$(VERSION).tgz has been created"
	git tag v$(VERSION) -m "release v$(VERSION)"
	@echo "tag v$(VERSION) created"
	git push; git push --tags
	@echo "code pushed"