<?php

namespace App\Support;

final class OrderCancellationReasons
{
    /**
     * @return array<string, string>
     */
    public static function customer(): array
    {
        return [
            'changed_mind' => 'Thay đổi nhu cầu, không muốn mua nữa.',
            'ordered_wrong_product' => 'Đặt nhầm sản phẩm.',
            'change_variant_or_quantity' => 'Muốn thay đổi size/màu/số lượng.',
            'better_price_elsewhere' => 'Tìm được nơi bán giá tốt hơn.',
            'delivery_too_slow' => 'Thời gian giao hàng quá lâu.',
            'change_shipping_information' => 'Muốn đổi địa chỉ hoặc thông tin nhận hàng.',
            'duplicate_order' => 'Đặt trùng đơn hàng.',
            'other' => 'Lý do khác.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function admin(): array
    {
        return [
            'out_of_stock' => 'Sản phẩm đã hết hàng.',
            'cannot_contact_customer' => 'Không thể liên hệ với khách hàng.',
            'customer_requested' => 'Khách yêu cầu hủy.',
            'payment_failed' => 'Thanh toán không thành công.',
            'suspicious_order' => 'Đơn hàng bất thường hoặc nghi ngờ gian lận.',
            'invalid_shipping_information' => 'Sai thông tin giao hàng (địa chỉ, SĐT không hợp lệ).',
            'discontinued_product' => 'Sản phẩm ngừng kinh doanh.',
            'other' => 'Lý do khác.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function system(): array
    {
        return [
            'confirmation_timeout' => 'Cửa hàng không xác nhận đơn COD trong vòng 24 giờ.',
        ];
    }

    public static function label(?string $reason, ?string $cancelledBy): ?string
    {
        if (! $reason) {
            return null;
        }

        $reasons = match ($cancelledBy) {
            'admin' => self::admin(),
            'system' => self::system(),
            default => self::customer(),
        };

        return $reasons[$reason] ?? $reason;
    }

    public static function emailMessage(?string $reason, ?string $cancelledBy): string
    {
        if ($cancelledBy === 'customer' || $reason === 'customer_requested') {
            return 'Theo yêu cầu của bạn, đơn hàng đã được hủy thành công.';
        }

        return match ($reason) {
            'confirmation_timeout' => 'Đơn COD không được cửa hàng xác nhận trong vòng 24 giờ nên hệ thống đã tự động hủy và trả lại số lượng hàng đang giữ.',
            'out_of_stock' => 'Rất tiếc, sản phẩm trong đơn hàng hiện đã hết hàng nên chúng tôi không thể tiếp tục xử lý đơn hàng của bạn.',
            'cannot_contact_customer' => 'Chúng tôi đã nhiều lần cố gắng liên hệ để xác nhận đơn hàng nhưng không thành công, vì vậy đơn hàng đã được hủy để đảm bảo tiến độ xử lý.',
            'payment_failed' => 'Rất tiếc, giao dịch thanh toán của đơn hàng không thành công nên chúng tôi chưa thể tiếp tục xử lý đơn hàng.',
            'suspicious_order' => 'Vì lý do an toàn, đơn hàng có dấu hiệu bất thường và cần được hủy để bảo vệ khách hàng cũng như hệ thống.',
            'invalid_shipping_information' => 'Thông tin giao hàng chưa hợp lệ và chúng tôi không thể xác minh để tiếp tục xử lý đơn hàng.',
            'discontinued_product' => 'Rất tiếc, sản phẩm trong đơn hàng đã ngừng kinh doanh nên chúng tôi không thể tiếp tục xử lý đơn hàng.',
            default => 'Rất tiếc, cửa hàng không thể tiếp tục xử lý đơn hàng và đã thực hiện hủy đơn.',
        };
    }
}
