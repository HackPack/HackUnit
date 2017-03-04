<?hh // strict

namespace HackPack\HackUnit\Report;

interface Format {
  public function writeReport(Summary $summary): void;
}
