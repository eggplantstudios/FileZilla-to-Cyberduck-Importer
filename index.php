<?php
/**
 * FileZilla to Cyberduck Importer
 *
 * This file, when executed in the browser, will convert your FileZilla.XML export file into a series of Cyberduck Bookmarks, which you can import into Cyberduck.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package    FileZilla to Cyberduck Importer
 * @author     Shawn Wernig  <shawn@eggplantstudios.ca>
 * @copyright  None
 * @version    1
 *
 */

// Set your FileZilla.xml path.
$PathToYourFileZillaExport = 'FileZilla.xml';


// Runs the Importer.
$import = new FileZillaToCyberduck( $PathToYourFileZillaExport );
$import->report();


/**
 * Class FileZillaToCyberduck
 *
 * The main importer. Send it the path to your FileZilla Export and it will run automatically.
 *
 */
class FileZillaToCyberduck
{
	/**
     * @var
     */
    private $bookmarks;
	/**
     * @var array
     */
    private $log = array();

	/**
     * FileZillaToCyberduck constructor.
     *
     * @param $path - Path to your fileZilla Export XML File.
     */
    public function __construct( $path )
    {
        $xml = simplexml_load_file( $path );
        if( $xml )
        {
            foreach($xml->xpath('//Server') as $server)
            {
             
                $bkmk = new Bookmark(array(
                    'hostname'  => (string) $server->Host,
                    'nickname'  => (string) $server->Name,
                    'port'      => (string) $server->Port,
                    'protocol'  => 'ftp', // todo: Fudged until we build the Protocol Translator
                    'uuid'      => false,
                    'username'  => (string) $server->User,
                    'password'  => (string) $server->Pass
                    ));

                $filename = $bkmk->writeToDuckFile();
                $this->log[] = sprintf('Saved %s', $filename );             
            }

        }
        
    }

	/**
     *
     * Reports back the import details to the screen.
     *
     */
    public function report()
    {
        printf('<strong>Created %d Bookmarks</strong>', count( $this->log ) );
        echo implode( '<br>', $this->log );
    }
}
 

 





class Bookmark {

	/**
     * @var
     */
    private $hostname, $nickname, $port, $protocol, $uuid, $username, $password;
	/**
     * @var array
     */
    private $protocols = array(
        // todo: Filezill has Protocol ID Numbers in place of strings. In the future translate these numbers into the string.
    );

	/**
     * Bookmark constructor.
     *
     * @param $args
     */
    public function __construct( $args )
    {
        foreach($args as $key => $val)
        {                
            if( property_exists($this, $key) )
            {
                switch( $key )
                {
                    default:
                    $this->{$key} = $val;
                }
            }
        }
    }

	/**
     * @param string $folder
     *
     * @return string
     */
    public function writeToDuckFile( $folder = 'cyberduck')
    {
            $dom = new DOMDocument;

            $dom->loadXML( $this->getAsXML() );

            if ( ! is_dir( $folder ) ) {
                mkdir( $folder, 0700);
            }
            $file = sprintf('%s/%s.duck',
                    $folder,
                    $this->nickname
                );
            //Save XML as a file
            $dom->save( $file ) or die("Could not save $folder/{$this->nickname}.duck");
            return $file;
    }

	/**
     * @return string
     */
    public function getAsXML()
    {
        return sprintf('<?xml version="1.0" encoding="UTF-8"?>
            <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
            <plist version="1.0">
            <dict>
                <key>Access Timestamp</key>
                <string>%s</string>
                <key>Hostname</key>
                <string>%s</string>
                <key>Nickname</key>
                <string>%s</string>
                <key>Port</key>
                <string>%s</string>
                <key>Protocol</key>
                <string>%s</string>
                <key>UUID</key>
                <string>%s</string>
                <key>Username</key>
                <string>%s</string>
                <key>Password</key>
                <string>%s</string>
            </dict>
            </plist>',
                time(),
                $this->hostname,
                $this->nickname,
                $this->port,
                $this->protocol,
                $this->uuid,
                $this->username,
                $this->password
            );
    }

}