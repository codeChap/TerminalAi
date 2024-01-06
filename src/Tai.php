<?php 

/**
 * Class Tai
 * 
 * The Tai class represents the terminal AI application.
 * It provides functionality to run commands, install Tai, and interact with OpenAI.
 */
class Tai
{
    /**
     * Run the Tai application.
     * 
     * @param array $config The configuration settings.
     * @param array $argv The command line arguments.
     * @return void
     */
    public function run($config, $argv)
    {
        // Check for the existence of the first argument
        if (empty($argv[1])) {
            print static::getHelpInfo();
            exit;
        }

        // Reserved commands
        switch($argv[1]){

            // Install Tai
            case 'install':
                $this->install($config, $argv);
                exit;
                break;

            // Help
            case 'help':
                print static::getHelpInfo();
                exit;
                break;

            // Clear messages
            case 'clear' : 
                $home   = getenv("HOME");
                $folder = $home . "/.config/tai";
                $file   = $folder . "/messages.txt";
                file_put_contents($file, "");
                print "Messages cleared.\n";
                exit;
                break;
        }

        // Set some variables
        $home   = getenv("HOME");
        $folder = $home . "/.config/tai";
        $file   = $folder . "/messages.txt";
        $key    = $folder . "/OpenAi.key";

        // Run existence checks
        if ( ! file_exists($folder)) {
            print "Tai needs to be setup, please run `tai install`.\n";
            exit;
        }
        if ( ! file_exists($file)) {
            print "Tai needs to be setup, please run `tai install`.\n";
            exit;
        }
        if ( ! file_exists($key)) {
            print "Tai needs to be setup, please run `tai install`.\n";
            exit;
        }

        // Combine args as a prompt
        $prompt = '';
        foreach($argv AS $k => $arg){
            if($k > 0){
                $prompt .= $arg . " ";
            }
        }

        // Get the last message
        $messages = file_get_contents($file);
        $messages = explode("\n", $messages);

        // Get the last 15 messages
        $messages = array_slice($messages, -15);

        // Decode each message
        $prompts = [];
        foreach($messages AS $k => $message){
            $prompts[] = json_decode($message, true);
        }

        // Add user prompt to the end
        $prompts[] = [
            "role"    => "user",
            "content" => trim($prompt),
        ];

        // Clean up
        $prompts = array_filter(array_values($prompts));

        // Run OpenAI
        $openai = new OpenAi;
        $openai->setKey(file_get_contents($key));
        $openai->setPrompts($prompts);
        $result = $openai->run();
        print PHP_EOL;

        // Build response for storage
        $prompts[] = [
            "role"    => "assistant",
            "content" => $result,
        ];

        // Store the response at ~/.config/tai/messages.txt
        $messages = '';
        foreach($prompts AS $prompt){
            $messages .= json_encode($prompt) . "\n";
        }
        file_put_contents($file, $messages);
    }

    /**
     * Install Tai.
     * 
     * @param array $config The configuration settings.
     * @param array $argv The command line arguments.
     * @return void
     */
    public function install($config, $argv)
    {
        // Set the folder for Tai
        $home = getenv("HOME");
        $folder = $home . "/.config/tai";

        // If the Tai folder doesn't exist, create it
        if ( ! file_exists($folder)) {
            print "Tai needs to create a folder at ~/.config/tai to store your messages and API key.\n";
            print "Is this ok? (y/n) ";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(trim($line) != 'y'){
                print "Aborting.\n";
                exit;
            }
            fclose($handle);
            print "\n";

            // Create the folder
            mkdir($folder);
        }

        // Check for the existence of the ~/.config/tai/messages.txt file
        $file = $folder . "/messages.txt";
        if ( ! file_exists($file)) {
            print "Tai needs to create a file at ~/.config/tai/messages.txt to store your messages.\n";
            print "Is this ok? (y/n) ";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(trim($line) != 'y'){
                print "Aborting.\n";
                exit;
            }
            fclose($handle);
            print "\n";

            // Create the file
            file_put_contents($file, "");
        }

        // Check for the existence of the ~/.config/tai/OpenAi.key file
        $file = $folder . "/OpenAi.key";
        if ( ! file_exists($file)) {
            print "Tai needs your OpenAI API key to run.\n";
            print "Please enter it now: ";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            fclose($handle);
            print "\n";

            // Create the file
            file_put_contents($file, $line);
        }

        // Let the user know they can alter the key at any time
        print "You can change your API key at any time by editing ~/.config/tai/OpenAi.key\n";
    }

    /**
     * Get the help info.
     */
    public static function getHelpInfo()
    {
return <<<EOT

Tai is a terminal AI application that uses OpenAI to generate responses based on given prompts.

Usage:
    tai [prompt]

Commands:
    install     Install Tai
    clear       Clear messages
    help        Show this help info
EOT . PHP_EOL . PHP_EOL;
    }
}