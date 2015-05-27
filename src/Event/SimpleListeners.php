<?hh // strict

namespace HackPack\HackUnit\Event;

type EndListener = (function():void);
type ExceptionListener = (function(\Exception):void);
type PassListener = (function():void);
type StartListener = (function():void);
type SuccessListener = (function():void);

