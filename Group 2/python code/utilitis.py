def check_bit(hex_string, bit_position):
    decimal_value = int(hex_string, 16)
    bit_value = (decimal_value >> bit_position) & 1
    return bit_value

def toggle_bit(hex_string, bit_position):
    decimal_value = int(hex_string, 16)
    toggled_value = decimal_value ^ (1 << bit_position)
    return hex(toggled_value)[2:]
