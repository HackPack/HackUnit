<?hh // strict

namespace HackPack\HackUnit\Event;

type ExceptionListener = (function(\Exception): void);
type FailureListener = (function(Failure): void);
type MalformedSuiteListener = (function(MalformedSuite): void);
type PassListener = (function(): void);
type RunEndListener = (function(): void);
type RunStartListener = (function(): void);
type SkipListener = (function(Skip): void);
type SuccessListener = (function(): void);
type SuiteEndListener = (function(): void);
type SuiteStartListener = (function(): void);
