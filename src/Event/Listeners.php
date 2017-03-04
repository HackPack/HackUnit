<?hh // strict

namespace HackPack\HackUnit\Event;

type BuildFailureListener = (function(BuildFailure): void);
type ExceptionListener = (function(\Exception): void);
type FailureListener = (function(Failure): void);
type MalformedSuiteListener = (function(MalformedSuite): void);
type PassListener = (function(Pass): void);
type RunEndListener = (function(): void);
type RunStartListener = (function(): void);
type SkipListener = (function(Skip): void);
type SuccessListener = (function(Success): void);
type SuiteEndListener = (function(SuiteEnd): void);
type SuiteStartListener = (function(SuiteStart): void);
type TestEndListener = (function(TestEnd): void);
type TestStartListener = (function(TestStart): void);
