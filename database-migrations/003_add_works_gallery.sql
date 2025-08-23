-- 施工実績にギャラリー画像用の配列カラムを追加
ALTER TABLE works
ADD COLUMN IF NOT EXISTS gallery_images TEXT[] DEFAULT '{}';



