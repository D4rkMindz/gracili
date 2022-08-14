<?php

/**
 * Handling email
 *
 * This function is shortening for filter_var.
 *
 * @param string|null $email to check
 *
 * @return bool
 * @see filter_var()
 */
function is_email(?string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}
