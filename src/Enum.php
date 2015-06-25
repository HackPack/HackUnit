<?hh // strict

namespace HackPack\HackUnit;

enum FileOutlineType : string
{
    clss = 'class';
    func = 'function';
    other = 'other';
}

enum CoverageLevel : int
{
    none = 0;
    summary = 1;
    full = 2;
}
