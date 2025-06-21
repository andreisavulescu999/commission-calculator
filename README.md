# Commission Calculator

A PHP service to calculate commission fees based on BIN (Bank Identification Number) country codes and currency exchange
rates.  
The commission rates differ for EU and non-EU countries.

---

## Features

- Determines country code from BIN using BinList API
- Converts transaction amounts to EUR based on exchange rates
- Applies different commission rates for EU and non-EU countries
- Correctly rounds commission fees (normal rounding for EU, ceiling for non-EU)
- Easily configurable commission rates via environment variables

---

## Requirements

- PHP 8.2+
- Composer
- Internet access for BIN and exchange rate APIs

---

## Installation

Clone the repository and install dependencies:

```bash
git clone
cd commission-calculator
composer install
