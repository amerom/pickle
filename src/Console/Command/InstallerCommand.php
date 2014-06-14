<?php
namespace Pickle\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use Pickle\Package;
use Pickle\BuildSrcUnix;

class InstallerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Install a php extension')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to the PECL extension root directory (default pwd), archive or extension name'
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $path = realpath($path);

        $pkg = new Package($path);
        $options = $pkg->getConfigureOptions();
		print_r($options);
        if ($options) {
		    $options_value = [];
            $helper = $this->getHelperSet()->get('question');

            foreach ($options['enable'] as $name => $opt) {
				print_r($opt);
				/* enable/with-<extname> */
				if ($name == $pkg->getName()) {
					$options_value[$name] = '';
					continue;
				}
                switch ($opt->default) {
                    case 'y':
                        $default = true;
                        break;
                    case 'n':
                        $default = false;
                        break;
                    default:
                        $default = $opt->default;
                        break;
                }
                $prompt = new ConfirmationQuestion($opt->prompt . " (default: " .$opt->default. "): ", $default);
                $options_value[$name] = $helper->ask($input, $output, $prompt);
            }
        }

        $bld = new BuildSrcUnix($pkg, $options_value);
        $bld->build();
    }
}