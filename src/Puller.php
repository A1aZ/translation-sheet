<?php

namespace Nikaia\TranslationSheet;

use Illuminate\Support\Collection;
use Nikaia\TranslationSheet\Commands\Output;
use Nikaia\TranslationSheet\Sheet\TranslationsSheet;
use Nikaia\TranslationSheet\Translation\Writer;

class Puller
{
    use Output;

    /** @var TranslationsSheet */
    protected $translationsSheet;

    /** @var Writer */
    protected $writer;

    public function __construct(TranslationsSheet $translationsSheet, Writer $writer)
    {
        $this->translationsSheet = $translationsSheet;
        $this->writer = $writer;

        $this->nullOutput();
    }

    public function pull()
    {
        $this->output->writeln('<comment>Pulling translation from Spreadsheet</comment>');
        $translations = $this->getTranslations();

        $this->output->writeln('<comment>Writing languages files :</comment>');
        $this->writer
            ->withOutput($this->output)
            ->setTranslations($translations)
            ->write();

        $this->output->writeln('<info>Done.</info>');
    }

    public function getTranslations()
    {
        $header = $this->translationsSheet->getSpreadsheet()->getCamelizedHeader();

        $translations = $this->translationsSheet->readTranslations();

        $translations = Util::keyValues($translations, $header);

        $locales = $this->translationsSheet->getSpreadsheet()->getLocales();
        $new_translations = new Collection();
        $translations->keyBy('fullKey')->each(function ($translations, $full_key) use ($locales, &$new_translations) {
            foreach ($translations as $key => $value) {
                if (in_array($key, $locales) && $value === $full_key) {
                    $translations[$key] = null;
                }
            }
            $new_translations[] = $translations;
        });

        return $new_translations;
    }
}
