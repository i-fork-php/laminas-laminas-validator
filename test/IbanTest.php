<?php

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Iban as IbanValidator;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function array_merge;
use function implode;

/**
 * @group      Laminas_Validator
 */
class IbanTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public function ibanDataProvider(): array
    {
        return [
            ['AD1200012030200359100100', true],
            ['AT611904300234573201',     true],
            ['AT61 1904 3002 3457 3201', true],
            ['AD1200012030200354100100', false],
            ['AL47212110090000000235698741', true],
            ['AD1200012030200359100100', true],
            ['AT611904300234573201', true],
            ['AZ21NABZ00000000137010001944', true],
            ['BH67BMAG00001299123456', true],
            ['BE68539007547034', true],
            ['BA391290079401028494', true],
            ['BG80BNBG96611020345678', true],
            ['CR0515202001026284066', true],
            ['HR1210010051863000160', true],
            ['CY17002001280000001200527600', true],
            ['CZ6508000000192000145399', true],
            ['DK5000400440116243', true],
            ['DO28BAGR00000001212453611324', true],
            ['EE382200221020145685', true],
            ['FO6264600001631634', true],
            ['FI2112345600000785', true],
            ['FR1420041010050500013M02606', true],
            ['GE29NB0000000101904917', true],
            ['DE89370400440532013000', true],
            ['GI75NWBK000000007099453', true],
            ['GR1601101250000000012300695', true],
            ['GL8964710001000206', true],
            ['GT82TRAJ01020000001210029690', true],
            ['HU42117730161111101800000000', true],
            ['IS140159260076545510730339', true],
            ['IE29AIBK93115212345678', true],
            ['IL620108000000099999999', true],
            ['IT60X0542811101000000123456', true],
            ['KZ86125KZT5004100100', true],
            ['KW81CBKU0000000000001234560101', true],
            ['LV80BANK0000435195001', true],
            ['LB62099900000001001901229114', true],
            ['LI21088100002324013AA', true],
            ['LT121000011101001000', true],
            ['LU280019400644750000', true],
            ['MK07250120000058984', true],
            ['MT84MALT011000012345MTLCAST001S', true],
            ['MR1300020001010000123456753', true],
            ['MU17BOMM0101101030300200000MUR', true],
            ['MD24AG000225100013104168', true],
            ['MC5811222000010123456789030', true],
            ['ME25505000012345678951', true],
            ['NL91ABNA0417164300', true],
            ['NO9386011117947', true],
            ['PK36SCBL0000001123456702', true],
            ['PL61109010140000071219812874', true],
            ['PT50000201231234567890154', true],
            ['RO49AAAA1B31007593840000', true],
            ['SM86U0322509800000000270100', true],
            ['SA0380000000608010167519', true],
            ['RS35260005601001611379', true],
            ['SK3112000000198742637541', true],
            ['SI56191000000123438', true],
            ['ES9121000418450200051332', true],
            ['SE4550000000058398257466', true],
            ['CH9300762011623852957', true],
            ['TN5910006035183598478831', true],
            ['TR330006100519786457841326', true],
            ['AE070331234567890123456', true],
            ['GB29NWBK60161331926819', true],
            ['VG96VPVG0000012345678901', true],
            ['DO17552081023122561803924090', true],
            ['SM76P0854009812123456789123', true],
            ['HR1723600001101234565', true],
            ['UA213223130000026007233566001', true],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider ibanDataProvider
     */
    public function testBasic(string $iban, bool $expected): void
    {
        $validator = new IbanValidator();
        $this->assertEquals(
            $expected,
            $validator->isValid($iban),
            implode("\n", array_merge($validator->getMessages()))
        );
    }

    public function testSettingAndGettingCountryCode(): void
    {
        $validator = new IbanValidator();

        $validator->setCountryCode('DE');
        $this->assertEquals('DE', $validator->getCountryCode());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ISO 3166-1');
        $validator->setCountryCode('foo');
    }

    public function testInstanceWithCountryCode(): void
    {
        $validator = new IbanValidator(['country_code' => 'AT']);
        $this->assertEquals('AT', $validator->getCountryCode());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ISO 3166-1');
        new IbanValidator(['country_code' => 'BAR']);
    }

    public function testSepaNotSupportedCountryCode(): void
    {
        $validator = new IbanValidator();
        $this->assertTrue($validator->isValid('DO17552081023122561803924090'));
        $validator->setAllowNonSepa(false);
        $this->assertFalse($validator->isValid('DO17552081023122561803924090'));
        $validator->setAllowNonSepa(true);
        $this->assertTrue($validator->isValid('DO17552081023122561803924090'));
    }

    public function testIbanNotSupportedCountryCode(): void
    {
        $validator = new IbanValidator();
        $this->assertFalse($validator->isValid('US611904300234573201'));
    }

    /**
     * @group Laminas-10556
     */
    public function testIbanDetectionWithoutCountryCode(): void
    {
        $validator = new IbanValidator();
        $this->assertTrue($validator->isValid('AT611904300234573201'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new IbanValidator();
        $this->assertSame(
            [
                IbanValidator::NOTSUPPORTED,
                IbanValidator::SEPANOTSUPPORTED,
                IbanValidator::FALSEFORMAT,
                IbanValidator::CHECKFAILED,
            ],
            array_keys($validator->getMessageTemplates())
        );
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testConstructorAllowsSettingOptionsViaOptionsArray(): void
    {
        $validator = new IbanValidator(['country_code' => 'AT', 'allow_non_sepa' => false]);
        $this->assertSame('AT', $validator->getCountryCode());
        $this->assertFalse($validator->allowNonSepa());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidValues(): array
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['foo']],
            'object'     => [(object) []],
        ];
    }

    /**
     * @dataProvider invalidValues
     * @param mixed $value
     */
    public function testIsValidReturnsFalseForNonStringValue($value): void
    {
        $validator = new IbanValidator();
        $this->assertFalse($validator->isValid([]));
    }
}
