<?php

namespace Anezi\Bundle\FontAwesomeBundle\Command;

use Curl\Curl;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use UnexpectedValueException;
use Version\Constraint;
use Version\Constraint\SimpleConstraint;
use Version\Operator;
use Version\Version;

class AssetsInstallCommand extends ContainerAwareCommand
{
    private $minimalSupportedVersion = '1.0.7';
    private $repository = 'FortAwesome/Font-Awesome';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fontawesome:assets:install')
            ->setDefinition(
                array(
                    new InputArgument(
                        'target',
                        InputArgument::OPTIONAL,
                        'The target directory',
                        'web'
                    ),
                    new InputOption(
                        'fontawesome-version',
                        'z',
                        InputOption::VALUE_OPTIONAL,
                        'The Font Awesome version',
                        $this->minimalSupportedVersion
                    ),
                )
            )
            ->setDescription('Installs Font Awesome web assets under a public web directory')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs Font Awesome bundle assets into a given
directory (e.g. the web directory).

<info>php %command.full_name% web</info>

The Font Awesome files will be copied into the web bundles directory.

EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($input->getArgument('target'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        $wantedVersion = $input->getOption('fontawesome-version');

        try {
            $versionConstraint = Constraint::parse($wantedVersion);
        } catch (UnexpectedValueException $e) {
            $output->writeln(
                '<error>Error:</error> <info>' .
                $wantedVersion . '</info> is not a valid version constraint.'
            );
            return;
        }

        $minimalConstraint =
            new SimpleConstraint(
                new Operator('>='),
                Version::parse($this->minimalSupportedVersion)
            );

        if(!$minimalConstraint->matches($versionConstraint)) {
            $output->writeln(
                '<error>Error:</error> <info>' .
                $wantedVersion . '</info> is not supported. Minimal version is: ' .
                $this->minimalSupportedVersion
            );
            return;
        }

        $curl = new Curl();
        $curl->get(
            'https://api.github.com' .
            '/repos/' . $this->repository . '/tags?per_page=100'
        );

        $versionToInstall = null;
        $versionToInstallObj = new Version(0, 0, 0);

        foreach(json_decode($curl->response) as $release) {
            try {
                $tag = Version::parse($release->name);
                if ($tag->getStability()->isStable()) {
                    $constraint = new SimpleConstraint(new Operator('='), $tag);
                    if($wantedVersion == '*' || $versionConstraint->isIncluding($constraint)) {
                        if ($tag->compare($versionToInstallObj) > 0) {
                            $versionToInstall = $release->name;
                            $versionToInstallObj = $tag;
                        }
                    } else {
                    }
                }
            } catch(\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        if(!$versionToInstall) {
            $output->writeln(
                '<error>Error:</error> <info>' .
                $wantedVersion . '</info> is greator than the last release. '
            );
            return;
        }

        /** @var Filesystem $filesystem */
        $filesystem = $this->getContainer()->get('filesystem');

        // Create the bundles directory otherwise symlink will fail.
        $bundlesDir = $targetArg . '/bundles/';
        $filesystem->mkdir($bundlesDir, 0777);

        $output->writeln(
            'Installing Font Awesome assets.'
        );

        $targetDir  = $bundlesDir . 'fontawesome-assets';

        $namespaceParts = explode('\\', __NAMESPACE__);
        array_pop($namespaceParts);

        $output->writeln(
            sprintf(
                'Installing assets for <comment>%s</comment> into <comment>%s</comment>',
                implode('\\', $namespaceParts),
                $targetDir
            )
        );

        $filesystem->remove($targetDir);

        $filesystem->mkdir($targetDir, 0777);

        $filesystem->mkdir($targetDir . '/tmp');

        $zip = file_get_contents(
            'https://github.com/' . $this->repository .
            '/archive/' . $versionToInstall . '.zip'
        );

        file_put_contents($targetDir . '/tmp/fontwesome.zip', $zip);

        $zip = new \ZipArchive;

        if ($zip->open($targetDir . '/tmp/fontwesome.zip') === TRUE) {
            $zip->extractTo($targetDir . '/tmp');
            $zip->close();

            $versionToInstall = str_replace('v', '', $versionToInstall);
            if(file_exists($targetDir . '/tmp/Font-Awesome-' . $versionToInstall)) {
                $filesystem->mirror(
                    $targetDir . '/tmp/Font-Awesome-' . $versionToInstall . '/css',
                    $targetDir . '/css'
                );
                if(file_exists($targetDir . '/tmp/Font-Awesome-' . $versionToInstall . '/font')) {
                    $filesystem->mirror(
                        $targetDir . '/tmp/Font-Awesome-' . $versionToInstall . '/font',
                        $targetDir . '/font'
                    );
                } elseif(file_exists($targetDir . '/tmp/Font-Awesome-' . $versionToInstall . '/fonts')) {
                    $filesystem->mirror(
                        $targetDir . '/tmp/Font-Awesome-' . $versionToInstall . '/fonts',
                        $targetDir . '/fonts'
                    );
                }
            }
            $filesystem->remove($targetDir . '/tmp');
        } else {
            throw new FileException('File Error');
        }
    }
}
