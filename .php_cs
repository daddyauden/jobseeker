<?php
if (!file_exists(__DIR__.'/src')) {
    exit(0);
}
return PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => array('syntax' => 'long'),
        'protected_to_private' => false,
    ))
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->append(array(__FILE__))
            ->exclude(array(
                // directories containing files with content that is autogenerated by `var_export`, which breaks CS in output code
                'Symfony/Component/DependencyInjection/Tests/Fixtures',
                'Symfony/Component/Routing/Tests/Fixtures/dumper',
                // fixture templates
                'Symfony/Component/Templating/Tests/Fixtures/templates',
                'Symfony/Bundle/FrameworkBundle/Tests/Templating/Helper/Resources/Custom',
                // generated fixtures
                'Symfony/Component/VarDumper/Tests/Fixtures',
                // resource templates
                'Symfony/Bundle/FrameworkBundle/Resources/views/Form',
            ))
            // file content autogenerated by `var_export`
            ->notPath('Symfony/Component/Translation/Tests/fixtures/resources.php')
            // autogenerated xmls
            ->notPath('Symfony/Component/Console/Tests/Fixtures/application_1.xml')
            ->notPath('Symfony/Component/Console/Tests/Fixtures/application_2.xml')
            // yml
            ->notPath('Symfony/Component/Yaml/Tests/Fixtures/sfTests.yml')
            // test template
            ->notPath('Symfony/Bundle/FrameworkBundle/Tests/Templating/Helper/Resources/Custom/_name_entry_label.html.php')
            // explicit heredoc test
            ->notPath('Symfony/Bundle/FrameworkBundle/Tests/Fixtures/Resources/views/translation.html.php')
            // purposefully invalid JSON
            ->notPath('Symfony/Component/Asset/Tests/fixtures/manifest-invalid.json')
    )
;
