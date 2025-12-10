# Architecture

System design, patterns, and architectural decisions behind Traitify.

## Overview

This section explains the core architecture of Traitify's generator system. Understanding these concepts will help you customize behavior, create custom generators, and make informed decisions about using the package.

## Table of Contents

### [1. Overview](01-overview.md)

High-level architecture, core components, layer responsibilities, and design patterns used throughout the package.

### [2. Generator Pattern](02-generator-pattern.md)

Deep dive into the ValueGenerator interface, AbstractValueGenerator base class, and how to create custom generators.

### [3. Resolution Strategy](03-resolution-strategy.md)

Understanding the three-tier resolution system (Model → Config → Default) and configuration merging.

## Related Documentation

- [Generators](../04-generators/README.md) - Built-in generators reference
- [Configuration](../05-configuration/README.md) - Configuration options
- [Advanced](../07-advanced/README.md) - Custom generators and extensions
